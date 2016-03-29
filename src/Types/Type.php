<?php

namespace Bozboz\Jam\Types;

use Bozboz\Jam\Templates\Template;
use Illuminate\Support\Fluent;

class Type extends Fluent implements \Bozboz\Admin\Base\ModelInterface
{
    protected $defaults = [
        'menu_title' => null,
        'name' => 'Unknown',
        'sorter' => \Bozboz\Jam\Types\Sorting\DefaultSort::class,
        'report' => \Bozboz\Admin\Reports\NestedReport::class,
        'link_builder' => null,
        'menu_builder' => \Bozboz\Jam\Types\Menu\Content::class,
        'entity' => \Bozboz\Jam\Entities\Entity::class
    ];

    public function entities()
    {
        return $this->templates()->get()->entities;
    }

    public function getHeading($plural)
    {
        return $plural ? str_plural($this->name) : $this->name;
    }

    public function templates()
    {
        return Template::whereTypeAlias($this->alias);
    }

    public function getEntity($attributes = [])
    {
        return $this->getObj('entity', $attributes);
    }

    public function getLinkBuilder()
    {
        return $this->getObj('link_builder');
    }

    public function isVisible()
    {
        return !is_null($this->get('link_builder', $this->defaults['link_builder']));
    }

    public function addToMenu($menu, $url)
    {
        return  $this->getObj('menu_builder')->buildMenu($this, $menu, $url);
    }

    public function getSorter()
    {
        return $this->getObj('sorter');
    }

    public function getReport($decorator)
    {
        return $this->getObj('report', $decorator);
    }

    protected function getObj($type, $arg = null)
    {
        $class = $this->get($type, $this->defaults[$type]);
        return new $class($arg);
    }

    public function getValidator()
    {
        # code...
    }

    public function sanitiseInput($input)
    {
        # code...
    }
}
