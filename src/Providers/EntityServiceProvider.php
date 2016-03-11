<?php

namespace Bozboz\Jam\Providers;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Fields\Field;
use Bozboz\Jam\Fields\FieldMapper;
use Bozboz\Jam\Types\Type;
use Illuminate\Support\ServiceProvider;

class EntityServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->bind(
			\Bozboz\Jam\Contracts\EntityRepository::class,
			\Bozboz\Jam\Entities\EntityRepository::class
		);

		$this->app->bind(
			\Bozboz\Jam\Contracts\LinkBuilder::class,
			\Bozboz\Jam\Entities\LinkBuilder::class
		);

		$this->app->singleton('FieldMapper', function ($app) {
			return new FieldMapper;
		});

		Field::setMapper($this->app['FieldMapper']);

		Entity::setLinkBuilder($this->app[\Bozboz\Jam\Contracts\LinkBuilder::class]);
	}

	public function boot()
	{
		$packageRoot = __DIR__ . '/../../';

		$this->loadViewsFrom("{$packageRoot}/resources/views", 'entities');

		$this->publishes([
			"{$packageRoot}database/migrations" => database_path('migrations')
		], 'migrations');

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

		$mapper->register('text',              \Bozboz\Jam\Fields\Text::class);
		$mapper->register('textarea',          \Bozboz\Jam\Fields\Textarea::class);
		$mapper->register('htmleditor',        \Bozboz\Jam\Fields\HTMLEditor::class);
		$mapper->register('image',             \Bozboz\Jam\Fields\Image::class);
		$mapper->register('gallery',           \Bozboz\Jam\Fields\Gallery::class);
		$mapper->register('date',              \Bozboz\Jam\Fields\Date::class);
		$mapper->register('date-time',         \Bozboz\Jam\Fields\DateTime::class);
		$mapper->register('email',             \Bozboz\Jam\Fields\Email::class);
		// $mapper->register('hidden',            \Bozboz\Jam\Fields\Hidden::class);
		$mapper->register('password',          \Bozboz\Jam\Fields\Password::class);
		// $mapper->register('select',            \Bozboz\Jam\Fields\Select::class);
		$mapper->register('toggle',            \Bozboz\Jam\Fields\Toggle::class);
		$mapper->register('foreign',           \Bozboz\Jam\Fields\Foreign::class);
		$mapper->register('entity-list-field', \Bozboz\Jam\Fields\EntityList::class);
		// $mapper->register('belongs-to-entity', \Bozboz\Jam\Fields\BelongsToEntity::class);
		// $mapper->register('belongs-to-type',   \Bozboz\Jam\Fields\BelongsToType::class);
	}
}
