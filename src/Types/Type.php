<?php

namespace Bozboz\Jam\Types;

use Bozboz\Jam\Templates\Template;

class Type implements \Bozboz\Admin\Base\ModelInterface
{
    protected $attributes = [
        'menu_title' => null,
        'name' => null,
        'report' => \Bozboz\Admin\Reports\Report::class,
        'link_builder' => \Bozboz\Jam\Entities\LinksDisabled::class,
        'menu_builder' => \Bozboz\Jam\Types\Menu\Content::class,
        'entity' => \Bozboz\Jam\Entities\Entity::class,
        'search_handler' => \Bozboz\Jam\Entities\NotIndexed::class,
        'decorator' => \Bozboz\Jam\Entities\EntityDecorator::class,
        'can_restrict_access' => false,
        'gated' => false,
    ];

    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

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

    public function canRestrictAccess()
    {
        return $this->can_restrict_access;
    }

    public function isGated()
    {
        return $this->gated;
    }

    protected function getLinkBuilder()
    {
        return $this->getObj('link_builder');
    }

    public function getSearchHandler()
    {
        return $this->getObj('search_handler');
    }

    public function getDecorator()
    {
        return $this->getObj('decorator');
    }

    public function updatePaths($entity)
    {
        $this->getLinkBuilder()->updatePaths($entity);
    }

    public function isVisible()
    {
        return $this->getLinkBuilder()->isVisible();
    }

    public function addToMenu($menu, $url)
    {
        return  $this->getObj('menu_builder')->buildMenu($this, $menu, $url);
    }

    public function getReport($decorator, $perPage)
    {
        return $this->getObj('report', $decorator, $perPage);
    }

    protected function getObj($type, $arg1 = null, $arg2 = null)
    {
        $class = $this->attributes[$type];
        if (is_callable($class)) {
            return call_user_func($class);
        } elseif (is_null($arg1)) {
            return app($class);
        } elseif ($class) {
            return new $class($arg1, $arg2);
        }
    }

    public function __get($attribute)
    {
        return array_key_exists($attribute, $this->attributes) ? $this->attributes[$attribute] : null;
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
