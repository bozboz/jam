<?php

namespace Bozboz\Jam\Types;

class NestedType extends Type
{
    protected $attributes = [
        'menu_title' => null,
        'name' => 'Unknown',
        'report' => \Bozboz\Admin\Reports\NestedReport::class,
        'link_builder' => null,
        'menu_builder' => \Bozboz\Jam\Types\Menu\Content::class,
        'entity' => \Bozboz\Jam\Entities\Entity::class
    ];
}
