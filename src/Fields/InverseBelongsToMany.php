<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\Field as AdminField;
use Bozboz\Admin\Fields\FieldGroup;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Http\Controllers\Admin\EntityController;
use Illuminate\Support\Facades\DB;

class InverseBelongsToMany extends BelongsToMany
{
    private $entity;

    public static function getDescriptiveName()
    {
        return 'Inverse Belongs To Many (read-only)';
    }

    protected function getRelationModel()
    {
        return Entity::class;
    }

    protected function getPivot()
    {
        return (object)[
            'table' => 'entity_entity',
            'other_key' => 'value_id',
            'foreign_key' => 'entity_id',
        ];
    }

    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new HiddenField($this->getInputName(), $this->getOption('type'));
    }

    public function getOptionFields()
    {
        return [
            new TypeSelectField('Type'),
            new TextField([
                'name' => 'options_array[per_page]',
                'label' => 'Per Page'
            ])
        ];
    }

    public function injectValue(Entity $entity, Value $value)
    {
        $this->entity = $entity;
        $entity->setAttribute($value->key, $this->getValue($value));
    }

    public function injectAdminValue(Entity $entity, Revision $revision)
    {
        $this->entity = $entity;
        $value = parent::injectAdminValue($entity, $revision);
        if ($value) {
            $entity->setAttribute($this->getInputName(), $this->getValue($value));
        }
    }

    public function getValue(Value $value)
    {
        $relation = app('EntityMapper')->get($this->getOption('type'))->getEntity();
        $results = DB::table('entity_entity')->select('entities.id')
            ->join('entity_values', 'entity_values.id', '=', 'entity_entity.value_id')
            ->join('entities', 'entities.revision_id', '=', 'entity_values.revision_id')
            ->where('entity_entity.entity_id', $this->entity->id)
            ->get();

        return $relation->withCanonicalPath()
            ->ofType($this->getOption('type'))
            ->whereIn('entities.id', collect($results)->pluck('id'))
            ->active()->ordered()
            ->paginate($this->getOption('per_page') ?: config('admin.listing_items_per_page'));
    }
}

class RelatedField extends AdminField
{
    public function __construct($attributes)
    {
        parent::__construct($attributes);
    }

    public function getInput()
    {
        $controller = app(EntityController::class);
        $actions = app('admin.actions');
        try {
            return $this->related->map(function($relation) use ($controller, $actions) {
                $action = $actions->edit(
                    '\\'.get_class($controller).'@edit',
                    [$controller, 'canEdit']
                )->setInstance($relation);
                return $relation->name . ' ' . $action->render();
            })->push($this->related->render())->implode('<br>');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
