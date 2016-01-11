<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Admin\Media\Media;
use Bozboz\Entities\Entities\Value;

class ImageField extends Field
{
	public function getAdminField(Value $value)
	{
		return new MediaBrowser($this->getValue($value), [
			'name' => $this->name
		]);
	}

	public function getvalue(Value $value)
	{
		return Media::forModel($value, 'value');
	}
}
