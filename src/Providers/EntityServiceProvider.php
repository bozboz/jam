<?php

namespace Bozboz\Entities\Providers;

use Bozboz\Entities\Fields\Field;
use Bozboz\Entities\Fields\FieldMapper;
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

		$this->app->bind(
			\Bozboz\Entities\Contracts\EntityRepository::class,
			\Bozboz\Entities\Entities\EntityRepository::class
		);

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
			];
		});
	}


	protected function registerFieldTypes()
	{
		$mapper = $this->app[FieldMapper::class];

		$mapper->register('text',              \Bozboz\Entities\Fields\TextField::class);
		$mapper->register('textarea',          \Bozboz\Entities\Fields\TextareaField::class);
		$mapper->register('htmleditor',        \Bozboz\Entities\Fields\HTMLEditorField::class);
		$mapper->register('image',             \Bozboz\Entities\Fields\ImageField::class);
		$mapper->register('gallery',           \Bozboz\Entities\Fields\GalleryField::class);
		$mapper->register('date',              \Bozboz\Entities\Fields\DateField::class);
		$mapper->register('date-time',         \Bozboz\Entities\Fields\DateTimeField::class);
		$mapper->register('email',             \Bozboz\Entities\Fields\EmailField::class);
		// $mapper->register('hidden',            \Bozboz\Entities\Fields\HiddenField::class);
		$mapper->register('password',          \Bozboz\Entities\Fields\PasswordField::class);
		// $mapper->register('select',            \Bozboz\Entities\Fields\SelectField::class);
		$mapper->register('toggle',            \Bozboz\Entities\Fields\ToggleField::class);
		$mapper->register('belongs-to-entity', \Bozboz\Entities\Fields\BelongsToEntityField::class);
		$mapper->register('belongs-to-type',   \Bozboz\Entities\Fields\BelongsToTypeField::class);
	}
}
