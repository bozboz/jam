<?php

namespace Bozboz\Jam\Types;

class NestedType extends Type
{
    protected $attributes = [
        'menu_title' => null,
        'name' => 'Unknown',
        'report' => \Bozboz\Admin\Reports\NestedReport::class,
        'link_builder' => \Bozboz\Jam\Entities\LinksDisabled::class,
        'menu_builder' => \Bozboz\Jam\Types\Menu\Content::class,
        'entity' => \Bozboz\Jam\Entities\Entity::class,
        'search_handler' => \Bozboz\Jam\Entities\NotIndexed::class,
        'decorator' => \Bozboz\Jam\Entities\EntityDecorator::class,
    ];
}
