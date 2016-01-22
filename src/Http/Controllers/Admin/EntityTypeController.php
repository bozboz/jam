<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Entities\Types\TypeDecorator;
use Bozboz\Entities\Types\TypeTemplatesAction;

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
