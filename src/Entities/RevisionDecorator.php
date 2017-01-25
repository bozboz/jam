<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Reports\Filters\ArrayListingFilter;
use Bozboz\Jam\Entities\Entity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Input;

class RevisionDecorator extends ModelAdminDecorator
{
	protected $entity;

	public function __construct(Revision $instance)
	{
		parent::__construct($instance);
	}

	public function getColumns($instance)
	{
		return [
			'Live Revision' => $instance->entity->currentRevision && $instance->entity->currentRevision->id === $instance->id
				? '<i class="fa fa-check"></i>'
				: false,
			'Author' => $instance->username,
			'Date' => $instance->created_at->format('d M Y H:i'),
			'Published At' => $instance->published_at && $instance->published_at < new Carbon ? $instance->published_at->format('d M Y H:i') : null,
			'Scheduled For' => $instance->published_at && $instance->published_at > new Carbon ? $instance->published_at->format('d M Y H:i') : null
		];
	}

	public function modifyListingQuery(Builder $query)
	{
		parent::modifyListingQuery($query);

		$query->with('entity.template', 'user');
	}

	public function getLabel($instance)
	{
		return $instance->entity->name;
	}

	public function getFields($instance)
	{
	}

	public function getHeading($plural = false)
	{
		return $this->entity->name.' '.parent::getHeading($plural);
	}

	/**
	 * Retrieve a full or paginated collection of instances of $this->model
	 *
	 * @param  boolean  $limit
	 * @return Illuminate\Pagination\Paginator
	 */
	public function getListingForEntity($id)
	{
		$entity = Entity::find($id);
		$this->entity = $entity;

		$query = $this->getModelQuery()->whereEntityId($id);

		if ($this->isSortable()) {
			return $query->get();
		}

		return $query->paginate(
			Input::get('per-page', $this->listingPerPageLimit())
		);
	}
}
