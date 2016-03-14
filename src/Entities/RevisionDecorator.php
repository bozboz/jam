<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Reports\Filters\ArrayListingFilter;
use Bozboz\Jam\Entities\Entity;
use Carbon\Carbon;

class RevisionDecorator extends ModelAdminDecorator
{
	public function __construct(Revision $instance)
	{
		parent::__construct($instance);
	}

	public function getColumns($instance)
	{
		return [
			'Live Revision' => $instance->entity->currentRevision && $instance->entity->currentRevision->id === $instance->id
				? '<i class="fa fa-check"></i>'
				: null,
			'Author' => $instance->username,
			'Date' => $instance->created_at->format('d M Y H:i'),
			'Sceduled For' => $instance->published_at > new Carbon ? $instance->published_at->format('d M Y H:i') : null
		];
	}

	public function getLabel($instance)
	{
		return $instance->entity->name;
	}

	public function getFields($instance)
	{
	}

	public function getListingFilters()
	{
		return [
			new ArrayListingFilter('entity_id', Entity::lists('name', 'id'))
		];
	}
}
