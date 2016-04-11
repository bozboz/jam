<?php

namespace Bozboz\Jam\Types;

use Bozboz\Admin\Reports\Actions\LinkAction;

class TypeTemplatesAction extends LinkAction
{
	protected $attributes = [
		'icon' => 'fa fa-file-o',
		'class' => 'btn-info',
	];

	public function getUrl()
	{
		return action($this->action, ['type' => $this->instance->alias]);
	}
}

