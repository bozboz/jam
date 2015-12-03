<?php

namespace Bozboz\Entities\Providers;

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

		if (! $this->app->routesAreCached()) {
			require "{$packageRoot}/src/Http/routes.php";
		}

		$this->buildAdminMenu();
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
}
