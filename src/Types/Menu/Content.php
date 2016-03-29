<?php

namespace Bozboz\Jam\Types\Menu;

class Content
{
	public function buildMenu($type, $menu, $url)
	{
		$menu[$type->name] = $url->route('admin.entities.index', ['type' => $type->alias]);
	}
}