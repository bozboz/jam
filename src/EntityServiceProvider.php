<?php

namespace Bozboz\Entities;

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

		$this->app['events']->listen('admin.renderMenu', function($menu)
		{
			$url = $this->app['url'];

			// $menu['Properties'] = [
			// 	'Featured' => $url->route('admin.properties.featured.index'),
			// 	'Branches' => $url->route('admin.branches.index'),
			// 	'Viewings' => $url->route('admin.viewings.index'),
			// 	'Postcodes' => $url->route('admin.postcodes.index'),
			// ];

			// $menu['Area Guides'] = $url->route('admin.areas.index');
			// $menu['Employees'] = $url->route('admin.employee.index');
			// $menu['Vacancies'] = $url->route('admin.vacancy.index');
			// $menu['Slideshows'] = $url->route('admin.slideshow.index');
			// $menu['Testimonials'] = $url->route('admin.testimonial.index');
			// $menu['Newsletter'] = $url->route('admin.newsletter.index');
			// $menu['Contact Submissions'] = $url->route('admin.contact-submissions.index');
		});
	}
}
