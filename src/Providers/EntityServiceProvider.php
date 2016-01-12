<?php

namespace Bozboz\Entities\Providers;

use Bozboz\Entities\Fields\Field;
use Bozboz\Entities\Fields\EntityRelationField;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Fields\ToggleField;
use Bozboz\Entities\Fields\DateField;
use Bozboz\Entities\Fields\DateTimeField;
use Bozboz\Entities\Fields\EmailField;
use Bozboz\Entities\Fields\HiddenField;
use Bozboz\Entities\Fields\HTMLEditorField;
use Bozboz\Entities\Fields\PasswordField;
use Bozboz\Entities\Fields\SelectField;
use Bozboz\Entities\Fields\TextField;
use Bozboz\Entities\Fields\TextareaField;
use Bozboz\Entities\Fields\GalleryField;
use Bozboz\Entities\Fields\ImageField;
use Bozboz\Entities\Fields\BelongsToTypeField;
use Bozboz\Entities\Fields\BelongsToEntityField;
use Bozboz\Entities\Types\Type;
use Illuminate\Support\ServiceProvider;

class EntityServiceProvider extends ServiceProvider
{
	public function register()
	{
	}

	public function boot()
	{
		$packageRoot = __DIR__ . '/../../';

		$this->loadViewsFrom("{$packageRoot}/resources/views", 'entities');

		$this->publishes([
			"{$packageRoot}database/migrations" => database_path('migrations')
		], 'migrations');

		$this->publishes([
			"{$packageRoot}/config/entities.php" => config_path('entities.php')
		], 'config');

		$this->app->singleton(FieldMapper::class, function ($app) {
			return new FieldMapper;
		});
		$this->registerFieldTypes();

		Field::setMapper($this->app[FieldMapper::class]);

		$this->buildAdminMenu();

		if (! $this->app->routesAreCached()) {
			require "{$packageRoot}/src/Http/routes.php";
		}
	}

	protected function buildAdminMenu()
	{
		$this->app['events']->listen('admin.renderMenu', function($menu)
		{
			$url = $this->app['url'];

			$contentMenu = $menu['Content'];

			$entityTypes = Type::all();
			foreach ($entityTypes as $type) {
				$contentMenu[$type->name] = $url->route('admin.entities.index', ['type' => $type->alias]);
			}
			$menu['Content'] = $contentMenu;

			$menu['Entities'] = [
				'Types' => $url->route('admin.entity-types.index'),
				// 'Templates' => $url->route('admin.entities.templates'),
				// 'Fields' => $url->route('admin.entities.fields'),
			];
		});
	}


	protected function registerFieldTypes()
	{
		$mapper = $this->app[FieldMapper::class];

		$mapper->register('text',              TextField::class);
		$mapper->register('textarea',          TextareaField::class);
		$mapper->register('toggle',            ToggleField::class);
		$mapper->register('date',              DateField::class);
		$mapper->register('date-time',         DateTimeField::class);
		$mapper->register('email',             EmailField::class);
		$mapper->register('hidden',            HiddenField::class);
		$mapper->register('htmleditor',        HTMLEditorField::class);
		$mapper->register('password',          PasswordField::class);
		$mapper->register('select',            SelectField::class);
		$mapper->register('image',             ImageField::class);
		$mapper->register('gallery',           GalleryField::class);
		$mapper->register('belongs-to-type',   BelongsToTypeField::class);
		$mapper->register('belongs-to-entity', BelongsToEntityField::class);
	}
}
