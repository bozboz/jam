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
			new SelectField('view', ['options' => $this->getViews(), 'class' => 'select2 form-control']),
			new SelectField('listing_view', ['options' => $this->getViews(), 'class' => 'select2 form-control']),
			new TextField('max_uses'),
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
