<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Fields\Field;
use Illuminate\Support\Facades\Auth;

class User extends Field
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new SelectField([
            'options' => $this->getUserOptions(),
            'name' => $this->getInputName(),
            'label' => $this->getInputLabel(),
            'help_text_title' => $this->help_text_title,
            'help_text' => $this->help_text,
            'value' => $value->exists ? $value->value : $this->getDefaultUser(),
        ]);
    }

    public function getOptionFields()
    {
        return [
            new CheckboxField([
                'name' => 'options_array[default_to_logged_in_user]',
                'label' => 'Default to logged in user',
                'checked' => true
            ])
        ];
    }

    protected function getDefaultUser()
    {
        return $this->getOption('default_to_logged_in_user') ? Auth::user()->id : null;
    }

    public function injectDiffValue(Entity $entity, Revision $revision)
    {
        $value = $revision->fieldValues->where('key', $this->name)->first() ?: new Value(['key' => $this->name]);
        $user = $this->getValue($value);
        if ($user) {
            $entity->setAttribute(
                $value->key,
                $user->first_name . ' ' . $user->last_name
            );
        }
        return $value;
    }

    protected function getUserOptions()
    {
        $users = app('Bozboz\Admin\Users\UserAdminDecorator');
        return $users->getListingModelsNoLimit()->map(function($user) use ($users) {
            $user->label = $users->getLabel($user);
            return $user;
        })->pluck('label', 'id')->prepend('-', '');
    }

    public function getValue(Value $value)
    {
        if ($value->value) {
            return Auth::getProvider()->createModel()->newQuery()->withTrashed()->whereId($value->value)->first();
        }
    }
}
