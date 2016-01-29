<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Reports\Actions\LinkAction;
use Bozboz\Entities\Entities\Revision;
use Carbon\Carbon;

class PublishAction extends LinkAction
{
	protected $defaults = [
		'warn' => null,
		'class' => 'btn-default',
		'label' => null,
		'icon' => null
	];

	public function __construct($actions, $permission = null, $attributes = [])
	{
		$this->actions = $actions;
		parent::__construct(null, $permission, $attributes);
	}

	public function getAttributes()
	{
		$attributes = $this->attributes;
		$currentRevision = $this->instance->currentRevision;

		switch ($currentRevision->status) {

			case Revision::PUBLISHED:
				$this->action = $this->actions[Revision::UNPUBLISHED];
				$attributes['label'] = 'Published <small>( '.$currentRevision->formatted_published_at.' )</small>';
				$attributes['class'] = 'btn-success';
			break;

			case Revision::UNPUBLISHED:
				$this->action = $this->actions[Revision::PUBLISHED];
				$attributes['label'] = 'Publish';
			break;

			case Revision::SCHEDULED:
				$this->action = $this->actions[Revision::UNPUBLISHED];
				$attributes['label'] = 'Scheduled (click to unpublish)';
				$attributes['class'] = 'btn-warning';
			break;

		}

		return $attributes;
	}
}
