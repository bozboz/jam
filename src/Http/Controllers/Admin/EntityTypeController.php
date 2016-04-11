<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Permissions\RestrictAllPermissionsTrait;
use Bozboz\Admin\Reports\Actions\CreateAction;
use Bozboz\Jam\Types\TypeDecorator;
use Bozboz\Jam\Types\TypeTemplatesAction;
use Illuminate\Support\Facades\Input;

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
				[$this, 'canEdit'],
				[
					'label' => 'See All'
				]
			),
			new TypeTemplatesAction(
				'\\'.EntityTemplateController::class.'@createForType',
				[app(EntityTemplateController::class), 'canCreate'],
				[
					'class' => 'btn-success btn-create btn btn-sm',
					'icon' => 'fa fa-plus',
					'label' => 'New Template'
				]
			)
		];
	}
}
