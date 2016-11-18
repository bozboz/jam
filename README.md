# Jam Package

## Contents

- [1. Installation](#1-installation)
- [2. Data Setup](#2-data-setup)
    - [2.1. Types](#21-types)
    - [2.2. Templates](#22-templates)
    - [2.3. Fields](#23-fields)
    - [2.4. Entities](#24-entities)
    - [2.5. Revisions](#25-revisions)
- [3. Usage](#3-usage)
    - [3.1. Catchall Route](#31-catchall-route)
    - [3.2. EntityRepository](#32-entity-repository)
    - [3.3. Listings & Other Data](#33-listings-other-data)
    - [3.4. Canonical Paths](#34-canonical-paths)
    - [3.5. Value Retrieval](#35-value-retrieval)
- [4. Search Indexing](#4-search-indexing)

---

## 1. Installation

1. Require the package in Composer, by running `composer require bozboz/jam`
2. Add `Bozboz\Jam\Providers\JamServiceProvider::class` to the providers array config/app.php
3. Run `php artisan vendor:publish && php artisan migrate` 

---

## 2. Data Setup

### 2.1. Types

Entity types are the top level of the jam schema. They can essentially be thought of as models, or a logical grouping of models since the templates actually have the fields. Types are defined in service providers. Jam comes with a "Pages" type out of the box since most apps are going to need one.

When you register a type you can give it a report, link builder, menu builder, search handler and entity. If any are left blank the default will be used. 

If you require nested sorting you should use the NestedType.

-   `report`
    Admin report class for listing. Generally this won't ever need to be changed unless you're going for something completely custom. The NestedType automatically switches the default to NestedReport.

    Default `Bozboz\Admin\Reports\Report`
    
-   `link_builder`
    This is the class responsible for knowing what paths/URLs the type it's responsible for should generate. By default a type won't generate paths since it's not assumed that every entity is a standalone page. If you need your type to generate paths you may use `Bozboz\Jam\Entities\LinkBuilder` which will generate a single path based on the nesting of the current entity and update the paths of any descendant entities. For more complex entities that need to exist under more than 1 URL you can extend the LinkBuilder class and add your own path generation logic. The main path generated from the entity nest will be used as the canonical path for any additional paths.
    
    Default `Bozboz\Jam\Entities\LinksDisabled`
    
-   `menu_builder`  
    Handles where to put the type in the admin menu. Types won't display in the menu until they have a [template](#templates) to base the entity on.  
    -   `Bozboz\Jam\Types\Menu\Hidden` don't display in menu
    -   `Bozboz\Jam\Types\Menu\Content` display in content dropdown
    -   `Bozboz\Jam\Types\Menu\Standalone` display as a top level menu item
    
    Default `Bozboz\Jam\Types\Menu\Content`

-   `search_handler`
    Jam has some basic support for Elastic Search built in but is disabled by default. See [4. Search Indexing](#4-search-indexing).

-   `entity` 
    There are a few different types of entity classes in Jam that dictate sorting options. The normal `Bozboz\Jam\Entities\Entity` isn't sortable and will be sorted by name. `Bozboz\Jam\Entities\SortableEntity` is manually sortable but alone will only allow sibling sorting. If nested sorting is required then it must be used in conjunction with the `Bozboz\Jam\Types\NestedType` type. Finally there's the `Bozboz\Jam\Entities\Post` entity that will sort the entities by date published.
    
    Default `Bozboz\Jam\Entities\Entity`

### 2.2. Templates

Once you've got some types you need to give them some templates. A template dictates what data the entity has. Types can have as many templates as you like. To add a template log in to the admin, click on "Jam" then pick a type to edit. 

When adding a template you must give it a `name` and `view` but the `listing view` and `max_uses` values are optional. The view field will be used in the default render method to pick what view to actually render. The implementation of listing_view is largely down to the requirements but its intention is that you could have multiple templates in a type that require different views in a listing. Once you've created a template the admin menu will acknowledge the type and display it in the the menu wherever the configured menu_builder dictates. `max_uses` allows you to limit the number of times a template may be used e.g. setting it to 1 will hide the option to create a new entity with that template after 1 is created.

### 2.3. Fields

A template is made up of a list of fields. Jam comes with the following field types:

- `Text`  
    Standard singe line text input.
    
- `Textarea`  
    Standard multiline text input with a checkbox option to make it a WYSIWYG HTML editor.
    
- `Image`  
    Single media library field.
    
- `Gallery`  
    Multiselect media library field.
    
- `Date`

- `Date & Time`

- `Toggle`  
    Checkbox for toggling a boolean value.
    
- `Entity List`  
    Allows creation of multiple child entities. Useful repeated content structures like slider slides, call out boxes, etc.  
    In order to use this field type you must first set up another entity type that this field can link to using the `Bozboz\Jam\Types\EntityList` type.  
    e.g.
    
    ```php?start_inline=1
    $mapper = $this->app['EntityMapper'];

    $mapper->register([
        'callout-boxes' => new \Bozboz\Jam\Types\EntityList('Callout Boxes'),
    ]);
    ```
    
- `Belongs To`  
    Allows you to link one entity to another. 
    
    en in the create/edit form and all entities created will have the selected parent. Selecting only a type or template will give the user the option of selecting the parent entity from entities from the selected type/templates.
    
    You may also select whether or not entities with this field become nested under the related entity as child pages. 
    
    **NOTE:** This should not be used with sortable entities that don't display in the same listing as resorting them can potentially lead to unexpected tree manipulation.
    
- `Belongs To Many`  
    Provides the entity form with an option to select from many entities to relate to. As with `Belongs To` the options in the dropdown will be limited to the type and template selected when adding the field to the template.

- `Inverse Belongs To Many (read-only)`  
    The reverse of the relationship defined in a `Belongs To Many` field, providing a read-only view to the related entities.
    
- `Hidden`  
    Allows you to add a hidden field to the create/edit form of entities that will save the value entered when the field is created. 

If you needed any functionality not listed above (eg. to define a relationship between an entity template and an app's custom model not stored in Jam) you may create any number of custom field types by extending the `Bozboz\Jam\Fields\Field` class and registering the field type in a service provider using the `FieldMapper` registered in the service container.

### 2.4. Entities

Generally you won't need more than the default functionality in the package and you shouldn't interact directly with the Entity class itself. See [2.4. Entities](#2-4-entities) for info on how to use different entity classes and [3.2. EntityRepository](#3-2-entityrepository) for info on how to fetch entities.

### 2.5. Revisions

Every time you save an entity it will create a revision in the entity_revisions table and a new set of values. This allows you to track changes across entities or revert back to previous states.

---

## 3. Usage

### 3.1. Catchall Route

Jam doesn't have any frontend routes set up by default but it does have a controller that your app can point some routes at. 

Generally you'll want to add a catchall route right at the end of your routes file which will handle most if not all of your entity routing. This will use the paths table to lookup the entity based on the request path and serve it up in the view its template has configured.

```php?start_inline=1
Route::get('{entityPath}', [
    'as' => 'entity',
    'uses' => '\Bozboz\Http\Controllers\EntityController@forPath'
])->where('entityPath', '(.+)?');
```

### 3.2. EntityRepository

Manual retrieval of entities should be done using the `forType` method on the `Bozboz\Jam\Repositories\EntityRepository` class. This will return a new query builder for the correct entity class registered to the type and limited to entities of that type.

### 3.3. Listings & Other Data

Some pages will require more data than just the entity so you will need to create a view composer for the current view. These should be added to `app/Http/ViewComposers` and registered in the boot method of a ComposerServiceProvider service provider.

### 3.4. Canonical Paths

Every entity with a link builder will have a canonical path which most of the time will be the path you'll want to use when linking to it from other pages/menus. When fetching a list of entities to link to you should use the `withCanonicalPath` scope in order to eager load the it and then use `$entity->canonical_path` to output it.

### 3.5. Value Retrieval

Just querying the entities will only give you the data from the entities table, in order to load the values you must call the `injectValues` method on each entity. When working with a collection of entities the values should be eager loaded using the `withFields` method before calling `injectValues`. 

e.g. 
```php?start_inline=1
$pages = $entityRepository->gotType('page')->withFields('content', 'image')->get()->map(function($page) {
    return $page->injectValues();
});
```

**NOTE:** If you are working with a paginator rather than a collection then you will need to use the `transform` method rather than map to work with the original paginator object so it maintains its functionality.

e.g.
```php?start_inline=1
$pages = $entityRepository->gotType('page')->withFields('content', 'image')->paginate();
$pages->transform(function($page) {
    return $page->injectValues();
});
```

---

## 4. Search Indexing

Jam supports indexing entities via elastic search but each type needs to be set up with a search handler, see [2.1. Types](#2-1-types). If all you need to be indexed is the name of the entity then you can just use the base `Bozboz\Jam\Entities\Indexer` class. In the likely event that you need more than that each type will need a handler written for it. When setting up a search handler class for an entity type it should extend the bas Indexer class and override the `getPreviewData` and `getSearchableData` methods. 

The purpose of `getPreviewData` is to transform the entity in to a suitable format for your search results view and `getSearchableData` is for returning all the values you want to be searchable as one long string.

e.g.
```php?start_inline=1
protected function getPreviewData($page)
{
    return [
        'preview' => str_limit(strip_tags(
            $page->intro_text ?: $page->content
        ), 200),
        'image' => Media::getFilenameOrFallback($page->image, null, 'search-result'),
    ];
}

protected function getSearchableData($page)
{
    return strip_tags(implode(' ', [
        $page->intro_text,
        $page->content,
    ]));
}
```