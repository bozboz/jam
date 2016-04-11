<?php

namespace Bozboz\Jam\Types;

use Bozboz\Jam\Templates\Template;
use Illuminate\Support\Fluent;

class Type extends Fluent implements \Bozboz\Admin\Base\ModelInterface
{
    protected $attributes = [
        'menu_title' => null,
        'name' => null,
        'report' => \Bozboz\Admin\Reports\Report::class,
        'link_builder' => \Bozboz\Jam\Entities\LinksDisabled::class,
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

    public function updatePaths($entity)
    {
        $linkBuilder = $this->getLinkBuilder();
        $linkBuilder->updatePaths($entity);
    }

    public function isVisible()
    {
        return !is_null($this->link_builder);
    }

    public function addToMenu($menu, $url)
    {
        return  $this->getObj('menu_builder')->buildMenu($this, $menu, $url);
    }

    public function getReport($decorator)
    {
        return $this->getObj('report', $decorator);
    }

    protected function getObj($type, $arg = null)
    {
        $class = $this->get($type);
        if (is_callable($class)) {
            return call_user_func($class);
        } elseif ($class) {
            return new $class($arg);
        }
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
