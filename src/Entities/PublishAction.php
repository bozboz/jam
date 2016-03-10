<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Reports\Actions\DropdownAction;
use Bozboz\Admin\Reports\Actions\DropdownItem;
use Bozboz\Admin\Reports\Actions\DropdownUnlinkedItem;
use Bozboz\Admin\Reports\Actions\LinkAction;
use Bozboz\Admin\Reports\ChecksPermissions;
use Bozboz\Jam\Entities\Revision;
use Carbon\Carbon;

class PublishAction extends DropdownAction
{
	protected $defaults = [
		'warn' => null,
		'btnClass' => 'btn-default',
		'dropdownClass' => '',
		'label' => null,
		'icon' => null,
		'compactSingleActionToLink' => false
	];

	public function getAttributes()
	{
		$attributes = $this->attributes;
		$currentRevision = $this->instance->currentRevision;

		switch ($currentRevision->status) {

			case Revision::PUBLISHED:
				$attributes['label'] = 'Published';
				$attributes['icon'] = 'fa-check';
				$attributes['btnClass'] = 'btn-success';
			break;

			case Revision::UNPUBLISHED:
				$attributes['label'] = 'Hidden';
				$attributes['icon'] = 'fa-times';
			break;

			case Revision::SCHEDULED:
				$attributes['label'] = 'Scheduled';
				$attributes['icon'] = 'fa-clock-o';
				$attributes['btnClass'] = 'btn-warning';
			break;

		}

		return $attributes;
	}
}
