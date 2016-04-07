<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;

class EntityListForeign extends Foreign
{
    protected $attributes = [
	    'type_alias' => 'foreign',
    	'name' => 'list_parent',
    	'validation' => 'required|exists:entities,id',
    ];

    public function saveImmediately()
    {
        return true;
    }
}