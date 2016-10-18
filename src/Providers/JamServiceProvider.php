<?php

namespace Bozboz\Jam\Providers;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\Events\EntityDeleted;
use Bozboz\Jam\Entities\Events\EntitySaved;
use Bozboz\Jam\Entities\Events\EntitySorted;
use Bozboz\Jam\Entities\Listeners\UpdatePaths;
use Bozboz\Jam\Entities\Listeners\UpdateSearchIndex;
use Bozboz\Jam\Entities\PublishAction;
use Bozboz\Jam\Fields\Field;
use Bozboz\Jam\Mapper;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;
use Illuminate\Support\ServiceProvider;

class JamServiceProvider extends ServiceProvider
{
    protected $listen = [
    ];

    protected $commands = [
        'Bozboz\Jam\Console\Commands\AddOldUrl',
        'Bozboz\Jam\Console\Commands\CountErrors',
        'Bozboz\Jam\Console\Commands\FixTree',
        'Bozboz\Jam\Console\Commands\RecalculatePaths',
    ];

    public function register()
    {
        $this->app->register('Sofa\Revisionable\Laravel\ServiceProvider');

        $this->commands($this->commands);

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

        $this->app['events']->listen(EntitySaved::class, UpdatePaths::class);
        $this->app['events']->listen(EntitySaved::class, UpdateSearchIndex::class);

        $this->app['events']->listen(EntitySorted::class, function($event) {
            $this->app['Sofa\Revisionable\Listener']->onUpdated($event->entity);
        });

        $this->app['events']->listen(EntityDeleted::class, UpdatePaths::class);
        $this->app['events']->listen(EntityDeleted::class, UpdateSearchIndex::class);
    }

    protected function buildAdminMenu()
    {
        $this->app['events']->listen('admin.renderMenu', function($menu)
        {
            $url = $this->app['url'];

            $typesWithTemplates = Template::groupBy('type_alias')->pluck('type_alias')->toArray();

            $entityTypes = $this->app['EntityMapper']->getAll()->each(function($type, $alias) use ($typesWithTemplates, $url, $menu) {
                if ($menu->gate('view_entity_type', $type->alias) && in_array($type->alias, $typesWithTemplates)) {
                    $type->addToMenu($menu, $url);
                }
            });

            if ($menu->gate('manage_entities')) {
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
            'belongs-to'               => \Bozboz\Jam\Fields\BelongsTo::class,
            'belongs-to-many'          => \Bozboz\Jam\Fields\BelongsToMany::class,
            'date'                     => \Bozboz\Jam\Fields\Date::class,
            'date-time'                => \Bozboz\Jam\Fields\DateTime::class,
            'embed'                    => \Bozboz\Jam\Fields\Oembed::class,
            'entity-list'              => \Bozboz\Jam\Fields\EntityList::class,
            'gallery'                  => \Bozboz\Jam\Fields\Gallery::class,
            'hidden'                   => \Bozboz\Jam\Fields\Hidden::class,
            'htmleditor'               => \Bozboz\Jam\Fields\HTMLEditor::class,
            'image'                    => \Bozboz\Jam\Fields\Image::class,
            'inverse-belongs-to-many'  => \Bozboz\Jam\Fields\InverseBelongsToMany::class,
            'text'                     => \Bozboz\Jam\Fields\Text::class,
            'textarea'                 => \Bozboz\Jam\Fields\Textarea::class,
            'toggle'                   => \Bozboz\Jam\Fields\Toggle::class,
            'user'                     => \Bozboz\Jam\Fields\User::class,
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
