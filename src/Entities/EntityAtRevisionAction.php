<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Reports\Actions\LinkAction;

class EntityAtRevisionAction extends LinkAction
{
	protected $attributes = [
		'label' => 'View',
		'icon' => 'fa fa-eye',
		'class' => 'btn-info',
	];

	public function getUrl()
	{
		return action($this->action, [
			'entity_id' => $this->instance->entity->id,
			'revision_id' => $this->instance->id
		]);
	}
}

