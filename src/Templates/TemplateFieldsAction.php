<?php

namespace Bozboz\Jam\Templates;

use Bozboz\Admin\Reports\Actions\LinkAction;

class TemplateFieldsAction extends LinkAction
{
	protected $attributes = [
		'label' => 'Fields',
		'icon' => 'fa fa-file-o',
		'class' => 'btn-default',
	];

	public function getUrl()
	{
		return action($this->action, ['template_id' => $this->instance->id]);
	}
}
