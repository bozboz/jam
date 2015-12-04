<?php

namespace Bozboz\Entities\Media;

use Bozboz\Admin\Fields\MediaBrowser as BaseMediaBrowser;
use Bozboz\Admin\Models\Media;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Html\FormFacade as Form;
use View;

class MediaBrowser extends BaseMediaBrowser
{
	/**
	 * Calculate name of inputs, based on type of relation
	 *
	 * @return string
	 */
	protected function calculateName()
	{
		return $this->attributes['name'];
	}
}
