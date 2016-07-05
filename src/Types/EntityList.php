<?php

namespace Bozboz\Jam\Types;

class EntityList extends Type
{
    protected $attributes = [
        'menu_title' => null,
        'name' => null,
        'report' => null,
        'link_builder' => \Bozboz\Jam\Entities\LinksDisabled::class,
        'menu_builder' => \Bozboz\Jam\Types\Menu\Hidden::class,
        'entity' => \Bozboz\Jam\Entities\SortableEntity::class,
        'search_handler' => \Bozboz\Jam\Entities\NotIndexed::class,
    ];

    public function __construct($name)
    {
        parent::__construct([
            'name' => $name
        ]);
    }
}
