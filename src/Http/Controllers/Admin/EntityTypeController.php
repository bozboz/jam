<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Permissions\RestrictAllPermissionsTrait;
use Bozboz\Admin\Reports\Report;
use Bozboz\Jam\Types\TypeDecorator;
use Bozboz\Jam\Types\TypeTemplatesAction;

class EntityTypeController extends ModelAdminController
{
	protected $useActions = true;

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

	protected function getListingReport()
	{
		return new Report($this->decorator);
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
					'class' => 'btn-primary',
					'label' => 'See All',
				]
			),
			new TypeTemplatesAction(
				'\\'.EntityTemplateController::class.'@createForType',
				[app(EntityTemplateController::class), 'canCreate'],
				[
					'class' => 'btn-success',
					'icon' => 'fa fa-plus',
					'label' => 'New Template'
				]
			)
		];
	}
}
