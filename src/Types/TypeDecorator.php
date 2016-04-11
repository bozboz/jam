<?php

namespace Bozboz\Jam\Types;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Http\Controllers\Admin\EntityTemplateController;
use Bozboz\Jam\Http\Controllers\Admin\EntityTemplateFieldController;
use Bozboz\Jam\Mapper;
use Bozboz\Jam\Templates\TemplateFieldsAction;
use Bozboz\Jam\Types\Type;

class TypeDecorator extends ModelAdminDecorator
{
	public function __construct(Type $instance)
	{
		parent::__construct($instance);
	}

	public function getColumns($instance)
	{
		return [
			'Name' => $this->getLabel($instance),
			'Templates' => $instance->templates()->get()->map(function($template) {
				$action = new TemplateFieldsAction(
					'\\'.EntityTemplateFieldController::class.'@index',
					[app(EntityTemplateController::class), 'canEdit'],
					[
						'class' => 'btn-info',
						'label' => $template->name,
					]
				);
				$action->setInstance($template);
				return view($action->getView(), $action->getViewData())->render();
			})->implode(' ')
		];
	}

	public function getLabel($instance)
	{
		return $instance->name;
	}

	public function getFields($instance)
	{
		return [
			new TextField('name'),
			($instance->exists ? new TextField('alias') : null),
			new CheckboxField('visible'),
			new CheckboxField('generate_paths'),
		];
	}

	public function getListingModels()
	{
		return app('EntityMapper')->getAll()->keys()->map(function($typeAlias) {
			return new Type([
				'id' => uniqid(),
				'alias' => $typeAlias,
				'name' => $typeAlias,
				'visible' => true
			]);
		});
	}
}
