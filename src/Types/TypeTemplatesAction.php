<?php

namespace Bozboz\Entities\Types;

use Bozboz\Admin\Reports\Actions\LinkAction;

class TypeTemplatesAction extends LinkAction
{
	protected $attributes = [
		'label' => 'Templates',
		'icon' => 'fa fa-file-o',
		'class' => 'btn-default',
	];

	public function getUrl($id = null)
	{
		return action($this->action, ['type_id' => $id]);
	}
}

