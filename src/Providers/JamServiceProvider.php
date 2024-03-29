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
use Bozboz\Permissions\Facades\Gate;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class JamServiceProvider extends ServiceProvider
{
    protected $listen = [
    ];

    protected $commands = [
        \Bozboz\Jam\Console\Commands\AddOldUrl::class,
        \Bozboz\Jam\Console\Commands\CountErrors::class,
        \Bozboz\Jam\Console\Commands\FixTree::class,
        \Bozboz\Jam\Console\Commands\RecalculatePaths::class,
        \Bozboz\Jam\Console\Commands\SeederMake::class,
        \Bozboz\Jam\Console\Commands\MakeAllSeeders::class,
    ];

    public function register()
    {
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
			$packageRoot . '/database/migrations/' => database_path('migrations')
		], 'migrations');

        $this->mergeConfigFrom(
            "{$packageRoot}config/jam.php", 'jam'
        );

        $this->registerPermissions();

        $this->registerEntityTypes();
        $this->registerFieldTypes();

        $this->registerActions();

        $this->buildAdminMenu();

        if (! $this->app->routesAreCached()) {
            require "{$packageRoot}src/Http/routes.php";
        }

        $this->app['events']->listen(EntitySorted::class, UpdatePaths::class);

        $this->app['events']->listen(EntitySaved::class, UpdatePaths::class);
        $this->app['events']->listen(EntitySaved::class, UpdateSearchIndex::class);

        $this->app['events']->listen(EntityDeleted::class, UpdatePaths::class);
        $this->app['events']->listen(EntityDeleted::class, UpdateSearchIndex::class);

        $this->app['router']->pushMiddlewareToGroup('web', \Bozboz\Jam\Http\Middleware\PreviewMode::class);
        $this->app['router']->middleware('force-preview-mode', \Bozboz\Jam\Http\Middleware\ForcePreviewMode::class);
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

            if (Gate::allows('manage_entities')) {
                $menu['Jam'] = [
                    'Templates' => $url->route('admin.entity-types.index'),
                    'Template History' => $url->route('admin.entity-template-history.index'),
                ];
            }
        });
    }

    protected function registerPermissions()
    {
        $this->app['permission.handler']->define([

            // Allows access to edit jam templates, clients should never be given this
            'manage_entities' => \Bozboz\Permissions\Rules\Rule::class,

            'publish_entity' => \Bozboz\Permissions\Rules\Rule::class,
            'hide_entity' => \Bozboz\Permissions\Rules\Rule::class,
            'schedule_entity' => \Bozboz\Permissions\Rules\Rule::class,

            'view_entity_type' => \Bozboz\Permissions\Rules\Rule::class,
            'create_entity_type' => \Bozboz\Permissions\Rules\Rule::class,
            'delete_entity_type' => \Bozboz\Permissions\Rules\Rule::class,
            'edit_entity_type' => \Bozboz\Permissions\Rules\Rule::class,

            'create_under_specific_entity' => \Bozboz\Permissions\Rules\Rule::class,
            'edit_specific_entity' => \Bozboz\Permissions\Rules\Rule::class,
            'delete_specific_entity' => \Bozboz\Permissions\Rules\Rule::class,
            'view_specific_entity' => \Bozboz\Permissions\Rules\Rule::class,
            'publish_specific_entity' => \Bozboz\Permissions\Rules\Rule::class,
            'hide_specific_entity' => \Bozboz\Permissions\Rules\Rule::class,
            'schedule_specific_entity' => \Bozboz\Permissions\Rules\Rule::class,

            'view_entity_archive' => \Bozboz\Permissions\Rules\Rule::class,
            'delete_entity_forever' => \Bozboz\Permissions\Rules\Rule::class,

            'view_entity_history' => \Bozboz\Permissions\Rules\Rule::class,
            'edit_entity_history' => \Bozboz\Permissions\Rules\Rule::class,

            'view_gated_entities' => \Bozboz\Permissions\Rules\GlobalRule::class,
            'gate_entities' => \Bozboz\Permissions\Rules\Rule::class,

            'expire_entity' => \Bozboz\Permissions\Rules\Rule::class,

            'view_gated_entity_type' => \Bozboz\Permissions\Rules\Rule::class,

        ]);
    }

    protected function registerEntityTypes()
    {
        $mapper = $this->app['EntityMapper'];

        $mapper->register([
            'pages' => new \Bozboz\Jam\Types\NestedType([
                'name' => 'Pages',
                'entity' => \Bozboz\Jam\Entities\SortableEntity::class,
                'link_builder' => \Bozboz\Jam\Entities\Contracts\LinkBuilder::class,
                'can_restrict_access' => true,
            ]),
        ]);
    }

    protected function registerFieldTypes()
    {
        $mapper = $this->app['FieldMapper'];

        $mapper->register([
            'belongs-to'               => \Bozboz\Jam\Fields\BelongsToEntity::class,
            'belongs-to-many'          => \Bozboz\Jam\Fields\BelongsToManyEntity::class,
            'date'                     => \Bozboz\Jam\Fields\Date::class,
            'date-time'                => \Bozboz\Jam\Fields\DateTime::class,
            'select'                   => \Bozboz\Jam\Fields\Dropdown::class,
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
            'link'                     => \Bozboz\Jam\Fields\Link::class,
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
