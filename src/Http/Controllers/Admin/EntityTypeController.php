<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Permissions\RestrictAllPermissionsTrait;
use Bozboz\Admin\Reports\Actions\Action;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Admin\Reports\Actions\Presenters\Urls\Custom;
use Bozboz\Admin\Reports\Report;
use Bozboz\Jam\Types\TypeDecorator;
use Bozboz\Jam\Types\TypeTemplatesAction;

class EntityTypeController extends ModelAdminController
{
	protected $useActions = true;
	protected $useAnythingPermissions = false;

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
		$controller = app(EntityTemplateController::class);

		return [
			$this->actions->custom(
				new Link(new Custom(function($instance) use ($controller) {
					return action($controller->getActionName('index'), [
						'type' => $instance->alias
					]);
				}), 'See All', 'fa fa-file-o', ['class' => 'btn-primary']),
				new IsValid([$this, 'canEdit'])
			),
			$this->actions->custom(
				new Link(new Custom(function($instance) use ($controller) {
					return action($controller->getActionName('createForType'), [
						'type' => $instance->alias
					]);
				}), 'New Template', 'fa fa-plus', ['class' => 'btn-success']),
				new IsValid([$controller, 'canCreate'])
			)
		];
	}
}
