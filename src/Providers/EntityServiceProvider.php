<?php

namespace Bozboz\Entities\Providers;

use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Admin\Fields\DateField;
use Bozboz\Admin\Fields\DateTimeField;
use Bozboz\Admin\Fields\EmailField;
use Bozboz\Admin\Fields\HTMLEditorField;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Admin\Fields\PasswordField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Fields\TextareaField;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Fields\TemplateField;
use Bozboz\Entities\Fields\ImageTemplateField;
use Bozboz\Entities\Fields\GalleryTemplateField;
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

		// $mapper->register('belongs-to',      'Bozboz\Admin\Fields\BelongsToField');
		// $mapper->register('belongs-to-many', 'Bozboz\Admin\Fields\BelongsToManyField');
		$mapper->register('checkbox',   TemplateField::class, CheckboxField::class);
		$mapper->register('date',       TemplateField::class, DateField::class);
		$mapper->register('date-time',  TemplateField::class, DateTimeField::class);
		$mapper->register('email',      TemplateField::class, EmailField::class);
		$mapper->register('hidden',     TemplateField::class, HiddenField::class);
		$mapper->register('htmleditor', TemplateField::class, HTMLEditorField::class);
		$mapper->register('password',   TemplateField::class, PasswordField::class);
		$mapper->register('select',     TemplateField::class, SelectField::class);
		$mapper->register('text',       TemplateField::class, TextField::class);
		$mapper->register('textarea',   TemplateField::class, TextareaField::class);

		$mapper->register('image',   ImageTemplateField::class,   MediaBrowser::class);
		$mapper->register('gallery', GalleryTemplateField::class, MediaBrowser::class);
	}
}
