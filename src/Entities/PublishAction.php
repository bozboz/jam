<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Reports\Actions\DropdownAction;
use Bozboz\Admin\Reports\Actions\Presenters\Dropdown;
use Bozboz\Jam\Entities\Revision;
use Carbon\Carbon;

class PublishAction extends DropdownAction
{
	public function __construct($items)
	{
		parent::__construct($items, null);
	}

	public function output()
	{
		$currentRevision = $this->instance->currentRevision;
		$status = $currentRevision ? $currentRevision->status : false;
		$attributes = $this->attributes;

		switch ($status) {

			case Revision::PUBLISHED:
				$label = 'Published';
				$icon = 'fa-check';
				$attributes['class'] = 'btn-success btn-sm';
			break;

			case Revision::SCHEDULED:
				$label = 'Scheduled';
				$icon = 'fa-clock-o';
				$attributes['class'] = 'btn-warning btn-sm';
			break;

			case Revision::PUBLISHED_WITH_DRAFTS:
				$label = 'Published (With Drafts)';
				$icon = 'fa-check';
				$attributes['class'] = 'btn-warning btn-sm';
			break;

			case Revision::EXPIRED:
				$label = 'Expired';
				$icon = 'fa-times';
				$attributes['class'] = 'btn-warning btn-sm';
			break;

			default:
				$label = 'Hidden';
				$icon = 'fa-times';
				$attributes['class'] = 'btn-default btn-sm';
			break;

		}

		$presenter = new Dropdown($this->validItems, $label, $icon, $attributes);

		return $presenter->render();
	}
}
