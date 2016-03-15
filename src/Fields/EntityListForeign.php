<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;

class EntityListForeign extends Foreign
{
    protected $attributes = [
    	'name' => 'entity_id',
    	'validation' => 'required|exists:entities,id',
    ];
}