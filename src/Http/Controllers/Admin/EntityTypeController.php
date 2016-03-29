<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Permissions\RestrictAllPermissionsTrait;
use Bozboz\Jam\Types\TypeDecorator;
use Bozboz\Jam\Types\TypeTemplatesAction;

class EntityTypeController extends ModelAdminController
{
	use RestrictAllPermissionsTrait;

	public function __construct(TypeDecorator $decorator)
	{
		parent::__construct($decorator);
	}

	public function getRestrictRule()
	{
		return 'manage_entities';
	}

	protected function getReportActions()
	{
		return [];
	}

	/**
	 * Return an array of actions each row can perform
	 *
	 * @return array
	 */
	protected function getRowActions()
	{
		return [
			new TypeTemplatesAction(
					'\\'.EntityTemplateController::class.'@index',
					[$this, 'canEdit']
			)
		];
	}
}
