<?php

namespace Bozboz\Jam\Providers;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Fields\Field;
use Bozboz\Jam\Fields\FieldMapper;
use Bozboz\Jam\Types\Type;
use Illuminate\Support\ServiceProvider;

class JamServiceProvider extends ServiceProvider
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

			$entityTypes = Type::whereVisible(true)->get();

			foreach ($entityTypes as $type) {
				if ($menu->gate('view_entity_type', $type)) {
					$menu[$type->name] = $url->route('admin.entities.index', ['type' => $type->alias]);
				}
			}

			if ($menu->gate('manage_entities')) {
				$menu['Entities'] = [
					'Types' => $url->route('admin.entity-types.index'),
				];
			}
		});
	}


	protected function registerFieldTypes()
	{
		$mapper = $this->app['FieldMapper'];

		$mapper->register([
			'text'              => \Bozboz\Jam\Fields\Text::class,
			'textile'           => \Bozboz\Jam\Fields\Textile::class,
			'textarea'          => \Bozboz\Jam\Fields\Textarea::class,
			'htmleditor'        => \Bozboz\Jam\Fields\HTMLEditor::class,
			'image'             => \Bozboz\Jam\Fields\Image::class,
			'gallery'           => \Bozboz\Jam\Fields\Gallery::class,
			'date'              => \Bozboz\Jam\Fields\Date::class,
			'date-time'         => \Bozboz\Jam\Fields\DateTime::class,
			'email'             => \Bozboz\Jam\Fields\Email::class,
			'password'          => \Bozboz\Jam\Fields\Password::class,
			'toggle'            => \Bozboz\Jam\Fields\Toggle::class,
			'foreign'           => \Bozboz\Jam\Fields\Foreign::class,
			'entity-list-field' => \Bozboz\Jam\Fields\EntityList::class,
		]);
	}
}
