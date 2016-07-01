<?php

namespace Bozboz\Jam\Repositories;

use Bozboz\Jam\Entities\CurrentValue;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityPath;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Repositories\Contracts\EntityRepository as EntityRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\Collection;

class EntityRepository implements EntityRepositoryInterface
{
    protected $mapper;
    protected $query;

    function __construct()
    {
        $this->mapper = app()->make('EntityMapper');
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

    public function forType($typeAlias)
    {
        return $this->mapper->get($typeAlias)->getEntity()->whereHas('template', function($query) use ($typeAlias) {
            $query->where('type_alias', $typeAlias);
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
        if ($this->requiresNewRevision($entity, $input)) {
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

            return $revision;
        }
    }

    public function requiresNewRevision(Entity $entity, $input)
    {
        $latestRevision = $entity->latestRevision();

        if ($latestRevision) {
            $templateFields = $entity->template->fields->lists('name')->all();

            $currentValues = array_merge(
                array_fill_keys($templateFields, null),
                $latestRevision->fieldValues()->lists('value', 'key')->all()
            );
            $currentValues['status'] = $entity->status;
            $currentValues['published_at'] =  $latestRevision->getFormattedPublishedAtAttribute('Y-m-d H:i:s');

            $input['published_at'] = $input['currentRevision']['published_at'];
        }
        return !$latestRevision || count($this->diffValues($currentValues, $input)) > 0;
    }

    protected function diffValues($array1, $array2)
    {
        foreach($array1 as $key => $value)
        {
            if(is_array($value))
            {
                if(!isset($array2[$key]))
                {
                    $difference[$key] = $value;
                }
                elseif(!is_array($array2[$key]))
                {
                    $difference[$key] = $value;
                }
                else
                {
                    $new_diff = $this->diffValues($value, $array2[$key]);
                    if($new_diff != FALSE)
                    {
                        $difference[$key] = $new_diff;
                    }
                }
            }
            elseif(!isset($array2[$key]) || $array2[$key] != $value)
            {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? 0 : $difference;
    }
}
