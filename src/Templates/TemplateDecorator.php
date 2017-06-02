<?php

namespace Bozboz\Jam\Templates;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;

class TemplateDecorator extends ModelAdminDecorator
{
	protected $fieldMapper;

	public function __construct(Template $instance)
	{
		$this->fieldMapper = app('FieldMapper');
		parent::__construct($instance);
	}

	public function getColumns($instance)
	{
		$entityCount = $instance->entities->count();
		$trashedCount = $instance->entities()->onlyTrashed()->count();
		return [
			'Name' => $this->getLabel($instance),
			'Alias' => $instance->alias,
			'View' => $instance->view,
			'Listing View' => $instance->listing_view,
			'Entity Count' => $entityCount
				. ($instance->max_uses ?  '/' . $instance->max_uses : '')
				. ($trashedCount ? ' <small>(' . $trashedCount . ' deleted)</small>' : ''),
		];
	}

	public function getLabel($instance)
	{
		return $instance->name;
	}

	public function getHeading($plural = false)
	{
		return (request()->get('type') ? app('EntityMapper')->get(request()->get('type'))->name . ' ' : '')
			. str_plural('Template', $plural ? 2 : 1);
	}

	public function getFields($instance)
	{
		return [
			new TextField('type_alias', ['disabled']),
			new TextField('name'),
			($instance->exists ? new TextField('alias') : null),
			new SelectField('view', [
				'options' => $this->getViews(),
				'class' => 'select2 form-control',
				'help_text' => 'This will be used in the default render method to pick what view to actually render.'
			]),
			new SelectField('listing_view', [
				'options' => $this->getViews(),
				'class' => 'select2 form-control',
				'help_text' => 'The implementation of listing_view is largely down to the requirements but its intention is that you could have multiple templates in a type that require different views in a listing.',
			]),
			new TextField('max_uses', [
				'help_text' => 'Allows you to limit the number of times a template may be used e.g. setting it to 1 will hide the option to create a new entity with that template after 1 is created.'
			]),
			new HiddenField('type_alias')
		];
	}

	protected function getViews()
	{
		$path = base_path('resources/views');

		return collect(File::allFiles($path))->map(function($file) use ($path) {
			$remove = [$path . '/', '.blade.php'];
			return str_replace($remove, '', $file->getPathname());
		})->filter(function($file) {
			return preg_match('/^styleguide/', $file) !== 1;
		})->keyBy(function($value) {
			return str_replace('/', '.', $value);
		})->prepend('-', '')->all();
	}

	protected function modifyListingQuery(Builder $query)
	{
		$query->whereTypeAlias(Input::get('type'))->with('entities')->orderBy($this->model->sortBy());
	}
}
