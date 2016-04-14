<?php

namespace Bozboz\Jam\Types;

class EntityList extends Type
{
    protected $attributes = [
        'menu_title' => null,
        'name' => null,
        'report' => \Bozboz\Admin\Reports\Report::class,
        'link_builder' => \Bozboz\Jam\Entities\LinksDisabled::class,
        'menu_builder' => \Bozboz\Jam\Types\Menu\Hidden::class,
        'entity' => \Bozboz\Jam\Entities\SortableEntity::class
    ];

    public function __construct($nameOrAttributes)
    {
        if (!is_array($nameOrAttributes)) {
            $nameOrAttributes = [
                'name' => $nameOrAttributes
            ];
        }
        parent::__construct($nameOrAttributes);
    }
}