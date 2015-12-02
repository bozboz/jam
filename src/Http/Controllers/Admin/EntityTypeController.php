<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Controllers\ModelAdminController;

class ShippingMethodController extends ModelAdminController
{
	public function __construct(TypeDecorator $decorator)
	{
		parent::__construct($decorator);
	}
}
