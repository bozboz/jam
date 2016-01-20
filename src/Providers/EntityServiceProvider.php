<?php

namespace Bozboz\Entities\Providers;

use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Fields\Field;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Types\Type;
use Illuminate\Support\ServiceProvider;

class EntityServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->bind(
			\Bozboz\Entities\Contracts\EntityRepository::class,
			\Bozboz\Entities\Entities\EntityRepository::class
		);

		$this->app->bind(
			\Bozboz\Entities\Contracts\LinkBuilder::class,
			\Bozboz\Entities\Entities\LinkBuilder::class
		);

		$this->app->singleton('FieldMapper', function ($app) {
			return new FieldMapper;
		});

		Field::setMapper($this->app['FieldMapper']);

		Entity::setLinkBuilder($this->app[\Bozboz\Entities\Contracts\LinkBuilder::class]);
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

			$entityTypes = Type::whereVisible(true)->get();
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
		$mapper = $this->app['FieldMapper'];

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
		$mapper->register('foreign',           \Bozboz\Entities\Fields\ForeignField::class);
		$mapper->register('entity-list-field', \Bozboz\Entities\Fields\EntityListField::class);
		$mapper->register('belongs-to-entity', \Bozboz\Entities\Fields\BelongsToEntityField::class);
		$mapper->register('belongs-to-type',   \Bozboz\Entities\Fields\BelongsToTypeField::class);
	}
}
