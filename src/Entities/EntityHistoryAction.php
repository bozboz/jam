<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Reports\Actions\LinkAction;

class EntityHistoryAction extends LinkAction
{
	protected $attributes = [
		'label' => 'History',
		'icon' => 'fa fa-history',
		'class' => 'btn-default',
	];

	public function getUrl()
	{
		return action($this->action, ['entity_id' => $this->instance->id]);
	}
}

