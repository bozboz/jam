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

		$this->loadViewsFrom("{$packageRoot}/resources/views", 'admin');

		$this->publishes([
			"{$packageRoot}database/migrations" => database_path('migrations')
		], 'migrations');

		if (! $this->app->routesAreCached()) {
			require $packageRoot . '/src/Http/routes.php';
		}

		$this->buildAdminMenu();
	}

	protected function buildAdminMenu()
	{
		$this->app['events']->listen('admin.renderMenu', function($menu)
		{
			// $entityRepository = $this->app['entityRepository'];
			$url = $this->app['url'];

			$entityTypes = Type::all();
			foreach ($entityTypes as $type) {
				$menu[$type->name] = $url->route('admin.entities.index', [$type->alias]);
			}

			\Debugbar::info(Type::all());

			$menu['Entities'] = [
				// 'Types' => $url->route('admin.entities.types'),
				// 'Templates' => $url->route('admin.entities.templates'),
				// 'Fields' => $url->route('admin.entities.fields'),
			];
		});
	}
}
