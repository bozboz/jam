<?php

namespace Bozboz\Jam\Repositories;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityPath;
use Bozboz\Jam\Entities\Events\EntitySaved;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Repositories\Contracts\EntityRepository as EntityRepositoryInterface;
use Bozboz\Permissions\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Events\Dispatcher;

class EntityRepository implements EntityRepositoryInterface
{
    protected $mapper;
    private $event;

    function __construct(Dispatcher $event, Guard $auth)
    {
        $this->mapper = app()->make('EntityMapper');
        $this->auth = $auth;
        $this->event = $event;
    }

    public function find($id)
    {
        $entity = Entity::active()->whereId($id)->first();

        if (!$entity) {
            return false;
        }

        $entity->setAttribute('canonical', $entity->canonical_path);

        return $entity;
    }

    public function forType($typeAlias, $templateAlias = null)
    {
        return $this->mapper->get($typeAlias)->getEntity()->whereHas('template', function($query) use ($typeAlias, $templateAlias) {
            $query->where('type_alias', $typeAlias);

            if ($templateAlias) {
                $query->where('alias', $templateAlias);
            }
        })->ordered()->active()->authorised();
    }

    public function getForPath($path)
    {
        $query = EntityPath::wherePath($path);
        if (config('jam.deleted-mode')) {
            $query->withTrashed();
        }
        $path = $query->first();

        if (!$path) {
            return false;
        }

        $entity = $path->entity()->withFields(['*'])->withCanonicalPath()->active()->first();

        if (!$entity) {
            return false;
        }

        return $entity;
    }

    public function whereSlug($slug)
    {
        return Entity::whereSlug($slug)->first();
    }

    public function get301ForPath($path)
    {
        $path = EntityPath::wherePath($path)->onlyTrashed()->first();
        if ($path) {
            $redirectPath = EntityPath::whereEntityId($path->entity_id)->whereNull('canonical_id')->first();
            return $redirectPath ? $redirectPath->path : false;
        }
    }

    public function hydrate(Entity $entity)
    {
        $entity->setAttribute('breadcrumbs', $this->breadcrumbs($entity));

        $entity->injectValues();

        return $entity;
    }

    protected function breadcrumbs(Entity $entity)
    {
        return $entity->ancestors()->with('template')->active()->withCanonicalPath()->get()->push($entity)->map(function($crumb) {
            return (object) [
                'url' => $crumb->canonical_path,
                'label' => $crumb->name
            ];
        });
    }

    public function newRevision(Entity $entity, $input)
    {
        $input = collect($input);

        if ($input->get('currentRevision')) {
            $publishedAt = key_exists('published_at', $input['currentRevision'])
                ? $input['currentRevision']['published_at']
                : $entity->freshTimestamp();
            $expiredAt = key_exists('expired_at', $input['currentRevision'])
                ? $input['currentRevision']['expired_at']
                : null;
        } else {
            $publishedAt = $input->get('published_at');
            $expiredAt = $input->get('expired_at');
        }

        $revision = $entity->revisions()->create(array_filter([
            'published_at' => $publishedAt,
            'expired_at' => $expiredAt,
            'user_id' => $input->get('user_id')
        ]));

        foreach ($entity->template->fields as $field) {
            $field->saveValue($revision, $input->get($field->getInputName()));
        }

        switch ($input->get('status')) {
            case 'publish':
                $entity->currentRevision()->associate($revision);
            break;

            case 'draft':
            default:
                // do nothing
            break;
        }

        $entity->save();

        $this->event->fire(new EntitySaved($entity));

        return $revision;
    }

    /**
     * Authorised if:
     *   - the entity has no defined roles
     *   - the authenticated user has the "view_gated_entitites" permission
     *   - the authenticated user's role matches the entity's
     *
     * @param  Bozboz\Jam\Entities\Entity  $entity
     * @return boolean
     */
    public function isAuthorised(Entity $entity)
    {
        return Gate::allows('view_gated_entities')
            || (
                $this->isAuthorisedForEntity($entity)
                && $this->isAuthorisedForType($entity)
            );
    }

    protected function isAuthorisedForEntity($entity)
    {
        return (
            $entity->roles->isEmpty()
            || (
                $this->auth->user()
                && $entity->roles->contains($this->auth->user()->role)
            ))
            && $this->isAuthorisedForEntityAncestors($entity);
    }

    protected function isAuthorisedForEntityAncestors($entity)
    {
        $ancestorRoles = collect();
        $entity->ancestors()->with('roles')->get()->each(function($ancestor) use ($ancestorRoles) {
            $ancestor->roles->each(function($role) use ($ancestorRoles) {
                $ancestorRoles->push($role);
            });
        });

        return $ancestorRoles->isEmpty()
            || (
                $this->auth->user()
                && $ancestorRoles->contains($this->auth->user()->role)
            );
    }

    protected function isAuthorisedForType($entity)
    {
        $type = $entity->template->type();
        return ! $type->isGated() || Gate::allows('view_gated_entity_type', $type->alias);
    }
}
