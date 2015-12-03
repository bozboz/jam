<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Entities\Types\TypeDecorator;

class EntityTypeController extends ModelAdminController
{
	public function __construct(TypeDecorator $decorator)
	{
		parent::__construct($decorator);
	}
}
