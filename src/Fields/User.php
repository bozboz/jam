<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
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
            'value' => $this->getDefaultUser(),
        ]);
    }

    protected function getDefaultUser()
    {
        return Auth::user()->id;
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
        return Auth::user()->find($value->value);
    }
}