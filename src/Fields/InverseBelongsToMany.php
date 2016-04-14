<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\Field as AdminField;
use Bozboz\Admin\Fields\FieldGroup;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Illuminate\Support\Facades\DB;

class InverseBelongsToMany extends BelongsTo
{
    private $netity;

    public static function getDescriptiveName()
    {
        return 'Inverse Belongs To Many (read-only)';
    }

    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new FieldGroup($this->getInputLabel(), [
            new HiddenField($this->getInputName(), $this->getOption('type')),
            new RelatedField($this->getValue($value)->get())
        ]);
    }

    public function getOptionFields()
    {
        return [
            new TypeSelectField('Type')
        ];
    }

    public function injectValue(Entity $entity, Value $value)
    {
        $this->entity = $entity;
        $entity->setValue($value, $this->getValue($value));
    }

    public function injectAdminValue(Entity $entity, Revision $revision)
    {
        $this->entity = $entity;
        $value = parent::injectAdminValue($entity, $revision);
        $entity->setAttribute($this->getInputName(), $this->getValue($value)->get());
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
            ->get();
    }
}

class RelatedField extends AdminField
{
    public function __construct($related)
    {
        parent::__construct('', ['related' => $related]);
    }

    public function getInput()
    {
        return $this->related->map(function($relation) {
            return "<a href='{$relation->canonical_url}'>{$relation->name}</a>";
        })->implode(' <br> ');
    }
}