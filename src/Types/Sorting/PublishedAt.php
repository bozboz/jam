<?php

namespace Bozboz\Jam\Types\Sorting;

class PublishedAt
{
	public function sortQuery($query)
	{
		$query->select('entities.*')
			->leftJoin('entity_revisions as order_join', 'entities.revision_id', '=', 'order_join.id')
			->orderByRaw('order_join.published_at is null desc')
			->orderBy('order_join.published_at', 'desc')
			->orderBy('order_join.created_at', 'desc');
	}

	public function isSortable()
	{
		return false;
	}
}