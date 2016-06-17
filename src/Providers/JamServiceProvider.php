<?php

namespace Bozboz\Jam\Providers;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\PublishAction;
use Bozboz\Jam\Fields\Field;
use Bozboz\Jam\Mapper;
use Bozboz\Jam\Types\Type;
use Bozboz\Permissions\RuleStack;
use Illuminate\Support\ServiceProvider;

class JamServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            \Bozboz\Jam\Repositories\Contracts\EntityRepository::class,
            \Bozboz\Jam\Repositories\EntityRepository::class
        );

        $this->app->bind(
            \Bozboz\Jam\Entities\Contracts\LinkBuilder::class,
            \Bozboz\Jam\Entities\LinkBuilder::class
        );

        $this->app->singleton('EntityMapper', function ($app) {
            return new Mapper;
        });
        Entity::setMapper($this->app['EntityMapper']);

        $this->app->singleton('FieldMapper', function ($app) {
            return new Mapper;
        });
        Field::setMapper($this->app['FieldMapper']);
    }

    public function boot()
    {
        $packageRoot = __DIR__ . '/../../';

        $this->loadViewsFrom("{$packageRoot}/resources/views", 'jam');

        $this->publishes([
            "{$packageRoot}database/migrations" => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            "{$packageRoot}config/jam.php" => config_path('jam.php')
        ]);

        $permissions = $this->app['permission.handler'];

        require __DIR__ . '/../permissions.php';

        $this->registerEntityTypes();
        $this->registerFieldTypes();

        $this->registerActions();

        $this->buildAdminMenu();

        if (! $this->app->routesAreCached()) {
            require "{$packageRoot}src/Http/routes.php";
        }
    }

    protected function buildAdminMenu()
    {
        $this->app['events']->listen('admin.renderMenu', function($menu)
        {
            $url = $this->app['url'];

            $entityTypes = $this->app['EntityMapper']->getAll()->each(function($type, $alias) use ($url, $menu) {
                if ($type->templates()->count()) {
                    $type->addToMenu($menu, $url);
                }
            });

            $stack = new RuleStack();
            $stack->add('manage_entities');
            if ($stack->isAllowed() && $menu->gate('manage_entities')) {
                $menu['Jam'] = [
                    'Types' => $url->route('admin.entity-types.index'),
                ];
            }
        });
    }

    protected function registerEntityTypes()
    {
        $mapper = $this->app['EntityMapper'];

        $mapper->register([
            'pages' => new \Bozboz\Jam\Types\NestedType([
                'name' => 'Pages',
                'entity' => \Bozboz\Jam\Entities\SortableEntity::class,
                'link_builder' => function() {
                    return app(\Bozboz\Jam\Entities\Contracts\LinkBuilder::class);
                }
            ]),
        ]);
    }

    protected function registerFieldTypes()
    {
        $mapper = $this->app['FieldMapper'];

        $mapper->register([
            'text'                     => \Bozboz\Jam\Fields\Text::class,
            'textarea'                 => \Bozboz\Jam\Fields\Textarea::class,
            'htmleditor'               => \Bozboz\Jam\Fields\HTMLEditor::class,
            'image'                    => \Bozboz\Jam\Fields\Image::class,
            'gallery'                  => \Bozboz\Jam\Fields\Gallery::class,
            'date'                     => \Bozboz\Jam\Fields\Date::class,
            'date-time'                => \Bozboz\Jam\Fields\DateTime::class,
            'toggle'                   => \Bozboz\Jam\Fields\Toggle::class,
            'entity-list'              => \Bozboz\Jam\Fields\EntityList::class,
            'belongs-to'               => \Bozboz\Jam\Fields\BelongsTo::class,
            'belongs-to-many'          => \Bozboz\Jam\Fields\BelongsToMany::class,
            'inverse-belongs-to-many'  => \Bozboz\Jam\Fields\InverseBelongsToMany::class,
            'hidden'                   => \Bozboz\Jam\Fields\Hidden::class,
        ]);
    }

    protected function registerActions()
    {
        $actions = $this->app['admin.actions'];

        $actions->register('publish', function($items) {
            return new PublishAction($items);
        });
    }
}
