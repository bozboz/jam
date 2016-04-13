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

-   `report` Admin report class for listing. Use NestedReport to enable nested sorting. Default `Bozboz\Admin\Reports\Report`
-   `link_builder` Simple class for creating paths and urls to entities. Default `Bozboz\Jam\Entities\LinksDisabled`
-   `menu_builder` Handles where to put the type in the admin menu. Default `Bozboz\Jam\Types\Menu\Content`
    -   `Hidden` don't display in menu
    -   `Content` display in content dropdown
    -   `Standalone` display as a top level menu item
-   `entity` Actual entity class to be used. Default `Bozboz\Jam\Entities\Entity`

## Templates

Once you've got some types you need to give them some templates. A template dictates what data the entity has. Types can have as many templates as you like. To add a template log in to the admin, click on "Jam" then pick a type to edit. When adding a template you must give it a name but the view, listing view and listing fields are optional. 

## Fields

A template is made up of a list of fields. Jam comes with the following field types:

- `text` 

    Standard singe line text input. The value will be put through Markdown upon front end retrieval to allow for some basic formatting.
- `textarea` 

    Exactly the same as `text` but multiline.
- `image` 

    Single media library field.
- `gallery` 

    Multiselect media library field.
- `date`

- `date-time`

- `toggle` 

    Checkbox for toggling a boolean value.
- `entity-list-field` 

    Allows creation of multiple child entities. Useful repeated content structures like slider slides, call out boxes, etc.
    In order to use this field type you must first set up another entity type that this field can link to. It would be best to make 
- `belongs-to-type` 

    This field allows you to link a type of entity to a template. When you add the field to a template it will give you dropdowns to select the type, and optionally template, that you want to link to the template you're adding the field to. This allows you to access all of the entities of the selected type from the view.
- `belongs-to-entity` 

    Allows you to link one entity to another. 
    
    en in the create/edit form and all entities created will have the selected parent. Selecting only a type or template will give the user the option of selecting the parent entity from entities from the selected type/templates.
    
    You may also select whether or not entities with this field become nested under the related entity as child pages. 
- `belongs-to-many-entities` 

    Provides the entity form with an option to select from many entities to relate to. As with `belongs-to-entity` the options in the dropdown will be limited to the type and template selected when adding the field to the template.
- `hidden` 

    Allows you to add a hidden field to the create/edit form of entities that will save the value entered when the field is created. 

## Entities

TODO

## Values

TODO

---

# Setting up an Entity List Field

The first thing you need to do when using the entity list field is set up the entity type and templates for the list. 

For example, if you wanted to create an entity list field for a set of calls to action then you'd add a type called something like "Call To Action" and give that a template. Once you've added the required fields to the template you must add an entity-list-foreign field to it. Then on the template that you want your new calls to action to display on you add an entity-list-field and select the call to action type in the field options. Now when you go to edit an entity with this template there will be a listing of call to action entities in the form. 

---

# Parent Entity Fields

When setting up this field type you have the option of selecting a type, a template and an entity. Selecting all 3 will make the field hidden in the create/edit form and all entities created will have the selected parent. Selecting only a type or template will give the user the option of selecting the parent entity from entities from the selected type/templates.