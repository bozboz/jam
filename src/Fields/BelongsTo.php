<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Types\Type;

abstract class BelongsTo extends Field
{
    /**
     * Get relation model
     *
     * @return string
     */
    abstract protected function getRelationModel();

    public function relation(Value $value)
    {
        return $value->belongsTo($this->getRelationModel(), 'foreign_key');
    }

    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new BelongsToField($decorator, $this->relation($value), [
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel(),
                'help_text_title' => $this->help_text_title,
                'help_text' => $this->help_text,
            ],
            function ($query) use ($value) {
                $this->filterAdminQuery($query, $value);
            }
        );
    }

    protected function filterAdminQuery($query, $value) {}

    public function getInputName()
    {
        if (property_exists($this->options_array, 'make_parent')) {
            return 'parent_id';
        } else {
            return parent::getInputName();
        }
    }

    public function injectDiffValue(Entity $entity, Revision $revision)
    {
        $value = $revision->fieldValues->where('key', $this->name)->first() ?: new Value(['key' => $this->name]);
        $this->injectValue($entity, $value);
        $entity->setAttribute(
            $value->key,
            $entity->getAttribute($value->key) ? $entity->getAttribute($value->key)->name : null
        );
        return $value;
    }

    protected function usesForeignKey()
    {
        return true;
    }
}
