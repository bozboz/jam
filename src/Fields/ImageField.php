<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Entities\Entities\Value;

class ImageField extends Field
{
	public function getAdminField(Value $value)
	{
		return new MediaBrowser($value->image(), [
			'name' => $this->name
		]);
	}
}
