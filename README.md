# Jam Package

## Installation

1. Require the package in Composer, by running `composer require bozboz/jam`
2. Add `Bozboz\Jam\Providers\JamServiceProvider::class` to the providers array config/app.php
3. Run `php artisan vendor:publish && php artisan migrate` 

---

## Data Setup

### Types

Entity types are the top level of the jam schema. They can essentially be thought of as models. Types are defined in service providers. Jam comes with a "Pages" type out of the box which is registered in the registerEntityTypes method of JamServiceProvider.

When you register a type you can give it a report, link builder, menu builder and entity.

-   `report`  
    Admin report class for listing. Use NestedReport to enable nested sorting.  
    Default `Bozboz\Admin\Reports\Report`
    
-   `link_builder`  
    Simple class for creating paths and urls to entities.  
    Default `Bozboz\Jam\Entities\LinksDisabled`
    
-   `menu_builder`  
    Handles where to put the type in the admin menu.  
    Default `Bozboz\Jam\Types\Menu\Content`
    -   `Bozboz\Jam\Types\Menu\Hidden` don't display in menu
    -   `Bozboz\Jam\Types\Menu\Content` display in content dropdown
    -   `Bozboz\Jam\Types\Menu\Standalone` display as a top level menu item
    
-   `entity`  
    Default `Bozboz\Jam\Entities\Entity`

## Templates

Once you've got some types you need to give them some templates. A template dictates what data the entity has. Types can have as many templates as you like. To add a template log in to the admin, click on "Jam" then pick a type to edit. 

When adding a template you must give it a `name` and `view` but the `listing view` and `listing fields` values are optional. The view field will be used in the default render method to pick what view to actually render. The listing fields can be used if entities in this template will be displayed on a listing view. This is useful if you have multiple templates in the same listing that require different views. The listing fields value should be a comma separated list of template field names that will be fetched when retrieving the listing. These should be kept to only the fields needed to reduce unnecessary queries. Leaving the field blank will select no field, entering "*" will select all.

## Fields

A template is made up of a list of fields. Jam comes with the following field types:

- `Text`  
    Standard singe line text input. The value will be put through Markdown upon front end retrieval to allow for some basic formatting.
    
- `Textarea`  
    Exactly the same as `text` but multiline.
    
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
    
    ```php
    $mapper = $this->app['EntityMapper'];

    $mapper->register([
        'callout-boxes' => new \Bozboz\Jam\Types\EntityList('Callout Boxes'),
    ]);
    ```
    
- `Belongs To`  
    Allows you to link one entity to another. 
    
    en in the create/edit form and all entities created will have the selected parent. Selecting only a type or template will give the user the option of selecting the parent entity from entities from the selected type/templates.
    
    You may also select whether or not entities with this field become nested under the related entity as child pages. 
    
- `Belongs To Many`  
    Provides the entity form with an option to select from many entities to relate to. As with `Belongs To` the options in the dropdown will be limited to the type and template selected when adding the field to the template.

- `Inverse Belongs To Many (read-only)`  
    The reverse of the relationship defined in a `Belongs To Many` field, providing a read-only view to the related entities.
    
- `Hidden`  
    Allows you to add a hidden field to the create/edit form of entities that will save the value entered when the field is created. 

## Entities

TODO

## Values

TODO