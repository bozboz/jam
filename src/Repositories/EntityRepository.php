<?php

namespace Bozboz\Jam\Repositories;

use Bozboz\Jam\Entities\CurrentValue;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityPath;
use Bozboz\Jam\Entities\Events\EntitySaved;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Repositories\Contracts\EntityRepository as EntityRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\Collection;

class EntityRepository implements EntityRepositoryInterface
{
    protected $mapper;
    protected $query;
    private $event;

    function __construct(Dispatcher $event)
    {
        $this->mapper = app()->make('EntityMapper');
        $this->event = $event;
    }

    public function newQuery($typeAlias)
    {
        $this->query = $this->mapper->get($typeAlias)->getEntity()->newQuery();
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
        });
    }

    public function getForPath($path)
    {
        $path = EntityPath::wherePath($path)->first();

        if (!$path) {
            return false;
        }

        $entity = $path->entity()->withFields(['*'])->active()->first();

        if (!$entity) {
            return false;
        }

        $entity->setAttribute('canonical', $path->canonical_path);

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
        return $entity->ancestors()->with('template')->active()->get()->push($entity)->map(function($crumb) {
            return (object) [
                'url' => $crumb->canonical_path,
                'label' => $crumb->name
            ];
        });
    }

    public function newRevision(Entity $entity, $input)
    {
        switch ($input['status']) {
            case Revision::SCHEDULED:
                $publishedAt = $input['currentRevision']['published_at'];
            break;

            case Revision::PUBLISHED:
                $publishedAt = !$input['currentRevision']['published_at'] || (new Carbon($input['currentRevision']['published_at']))->isFuture()
                    ? $entity->freshTimestamp()
                    : $input['currentRevision']['published_at'];
            break;

            default:
                $publishedAt = null;
        }
        $revision = $entity->revisions()->create([
            'published_at' => $publishedAt,
            'user_id' => $input['user_id']
        ]);

        foreach ($entity->template->fields as $field) {
            $field->saveValue($revision, $input[$field->getInputName()]);
        }

        if ($publishedAt) {
            $entity->currentRevision()->associate($revision);
        } else {
            $entity->currentRevision()->dissociate();
        }
        $entity->save();

        $this->event->fire(new EntitySaved($entity));

        return $revision;
    }
}
