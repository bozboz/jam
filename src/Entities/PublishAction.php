<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Reports\Actions\DropdownAction;
use Bozboz\Admin\Reports\Actions\DropdownItem;
use Bozboz\Admin\Reports\Actions\DropdownUnlinkedItem;
use Bozboz\Admin\Reports\Actions\LinkAction;
use Bozboz\Admin\Reports\ChecksPermissions;
use Bozboz\Entities\Entities\Revision;
use Carbon\Carbon;

class PublishAction extends DropdownAction
{
	protected $defaults = [
		'warn' => null,
		'btnClass' => 'btn-default',
		'dropdownClass' => '',
		'label' => null,
		'icon' => null
	];

	public function __construct($actions, $permission = null, $attributes = [])
	{
		$this->permission = $permission;
		$this->publishingActions = $actions;
		parent::__construct($actions, $attributes);
	}

	public function check(ChecksPermissions $context)
	{
		if ( ! $this->permission) return true;

		return $context->check($this->permission);
	}

	public function getAttributes()
	{
		$attributes = $this->attributes;
		$currentRevision = $this->instance->currentRevision;

		$publishAction = new DropdownItem(
			[$this->publishingActions[Revision::PUBLISHED], $this->instance->id],
			null,
			['label' => 'Publish']
		);
		$unpublishAction = new DropdownItem(
			[$this->publishingActions[Revision::UNPUBLISHED], $this->instance->id],
			null,
			['label' => 'Hide']
		);
		$scheduleAction = new DropdownItem(
			[$this->publishingActions[Revision::SCHEDULED], $this->instance->id],
			null,
			['label' => 'Schedule', 'class' => 'js-schedule-entity']
		);

		switch ($currentRevision->status) {

			case Revision::PUBLISHED:
				$this->actions = collect([
					new DropdownUnlinkedItem(
						"<small>Published on {$currentRevision->formatted_published_at}</small>"
					),
					$unpublishAction
				]);

				$attributes['label'] = 'Published';
				$attributes['icon'] = 'fa-check';
				$attributes['btnClass'] = 'btn-success';
			break;

			case Revision::UNPUBLISHED:
				$this->actions = collect([
					$publishAction,
					$scheduleAction
				]);

				$attributes['label'] = 'Hidden';
				$attributes['icon'] = 'fa-times';
			break;

			case Revision::SCHEDULED:
				$this->actions = collect([
					new DropdownUnlinkedItem(
						"<small>Scheduled for {$currentRevision->formatted_published_at}</small>"
					),
					$unpublishAction,
					$publishAction
				]);

				$attributes['label'] = 'Scheduled';
				$attributes['icon'] = 'fa-clock-o';
				$attributes['btnClass'] = 'btn-warning';
			break;

		}

		return $attributes;
	}
}
