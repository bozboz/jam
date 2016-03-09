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

		$permissions = $this->app['permission.handler'];

		require __DIR__ . '/../permissions.php';

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
				if ($menu->gate('view_entity_type', $type)) {
					$contentMenu[$type->name] = $url->route('admin.entities.index', ['type' => $type->alias]);
				}
			}
			if ($contentMenu) {
				$menu['Content'] = $contentMenu;
			}

			if ($this->app['permission.checker']->allows('view_anything')) {
				$menu['Entities'] = [
					'Types' => $url->route('admin.entity-types.index'),
				];
			}
		});
	}


	protected function registerFieldTypes()
	{
		$mapper = $this->app['FieldMapper'];

		$mapper->register('text',              \Bozboz\Entities\Fields\Text::class);
		$mapper->register('textarea',          \Bozboz\Entities\Fields\Textarea::class);
		$mapper->register('htmleditor',        \Bozboz\Entities\Fields\HTMLEditor::class);
		$mapper->register('image',             \Bozboz\Entities\Fields\Image::class);
		$mapper->register('gallery',           \Bozboz\Entities\Fields\Gallery::class);
		$mapper->register('date',              \Bozboz\Entities\Fields\Date::class);
		$mapper->register('date-time',         \Bozboz\Entities\Fields\DateTime::class);
		$mapper->register('email',             \Bozboz\Entities\Fields\Email::class);
		// $mapper->register('hidden',            \Bozboz\Entities\Fields\Hidden::class);
		$mapper->register('password',          \Bozboz\Entities\Fields\Password::class);
		// $mapper->register('select',            \Bozboz\Entities\Fields\Select::class);
		$mapper->register('toggle',            \Bozboz\Entities\Fields\Toggle::class);
		$mapper->register('foreign',           \Bozboz\Entities\Fields\Foreign::class);
		$mapper->register('entity-list-field', \Bozboz\Entities\Fields\EntityList::class);
		// $mapper->register('belongs-to-entity', \Bozboz\Entities\Fields\BelongsToEntity::class);
		// $mapper->register('belongs-to-type',   \Bozboz\Entities\Fields\BelongsToType::class);
	}
}
