<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Jam\Types\TypeDecorator;
use Bozboz\Jam\Types\TypeTemplatesAction;

class EntityTypeController extends ModelAdminController
{
	public function __construct(TypeDecorator $decorator)
	{
		parent::__construct($decorator);
	}

	/**
	 * Return an array of actions each row can perform
	 *
	 * @return array
	 */
	protected function getRowActions()
	{
		return array_merge([
			new TypeTemplatesAction(
					'\\'.EntityTemplateController::class.'@index',
					[$this, 'canEdit']
			)
		], parent::getRowActions());
	}
}
