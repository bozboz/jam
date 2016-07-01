<?php

namespace Bozboz\Jam\Types;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Reports\Actions\Action;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Admin\Reports\Actions\Presenters\Urls\Url;
use Bozboz\Jam\Http\Controllers\Admin\EntityTemplateController;
use Bozboz\Jam\Http\Controllers\Admin\EntityTemplateFieldController;
use Bozboz\Jam\Mapper;
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
			'Templates' => $instance->templates()->orderBy('name')->get()->map(function($template) {
				$templateFieldsUrl = new Url(action('\\'.EntityTemplateFieldController::class.'@index', [
					'template_id' => $template->id
				]));
				$action = new Action(
					new Link($templateFieldsUrl, $template->name, 'fa fa-list-ul', ['class' => 'btn-info']),
					new IsValid([app(EntityTemplateController::class), 'canView'])
				);
				$action->setInstance($template);
				return (string)$action->render();
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
		return app('EntityMapper')->getAll()->map(function($type) {
			$type->id = uniqid();
			return $type;
		})->sortBy('name');
	}
}
