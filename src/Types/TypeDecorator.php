<?php

namespace Bozboz\Jam\Types;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Reports\Actions\Action;
use Bozboz\Admin\Reports\Actions\DropdownAction;
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
			'Name' => str_replace(' ', '&nbsp', $this->getLabel($instance)),
			'Group' => $instance->menu_title ?: 'Content',
			'Templates' => $this->getTemplateLinks($instance->templates()->orderBy('name')->get()),
		];
	}

	protected function getTemplateLinks($templates)
	{
		return $templates->map(function($template) {
			$editUrl = new Url(action('\\'.EntityTemplateController::class.'@edit', $template->id));
			$fieldsUrl = new Url(action('\\'.EntityTemplateFieldController::class.'@index', [
				'template_id' => $template->id
			]));
			$duplicateUrl = new Url(action('\\'.EntityTemplateController::class.'@duplicate', $template->id));

			$options = [
				new Action(
					new Link($editUrl, 'Edit', 'fa fa-pencil'),
					new IsValid([app(EntityTemplateController::class), 'canView'])
				),
				new Action(
					new Link($fieldsUrl, 'Fields', 'fa fa-list-ul'),
					new IsValid([app(EntityTemplateController::class), 'canView'])
				),
				new Action(
					new Link($duplicateUrl, 'Duplicate', 'fa fa-copy'),
					new IsValid([app(EntityTemplateController::class), 'canView'])
				),
			];

			$action = new DropdownAction(
				$options, $template->name, 'fa fa-file-text', ['class' => 'btn-info']
			);
			$action->setInstance($template);
			return (string)$action->render();
		})->implode(' ');
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
		})->sortBy(function($type) {
			return $type->menu_title;
		});
	}
}
