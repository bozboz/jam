<?php

namespace Bozboz\Jam\Types\Menu;

class Standalone
{
	public function buildMenu($type, $menu, $url)
	{
		$entityMenu = $menu[$type->menu_title];
		$entityMenu[$type->name] = $url->route('admin.entities.index', ['type' => $type->alias]);
		$menu[$type->menu_title] =  $entityMenu;
	}
}