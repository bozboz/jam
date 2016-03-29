<?php

namespace Bozboz\Jam\Types;

use Bozboz\Admin\Reports\Actions\LinkAction;

class TypeTemplatesAction extends LinkAction
{
	protected $attributes = [
		'label' => 'Templates',
		'icon' => 'fa fa-file-o',
		'class' => 'btn-default',
	];

	public function getUrl()
	{
		return action($this->action, ['type' => $this->instance->alias]);
	}
}

