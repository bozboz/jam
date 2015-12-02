<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Models\Base;

class Field extends Base
{
	protected $table = 'entity_fields';

	protected $fillable = [
		'alias'
	];

	public function getValidator()
	{

	}
}
