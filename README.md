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

- `text` Standard singe line text input. The value will be put through Markdown upon front end retrieval to allow for some basic formatting.
- `textarea` Exactly the same as `text` but multiline.
- `htmleditor` Summernote WYSIWYG field.
- `image` Single media library field.
- `gallery` Multiselect media library field.
- `date` Datepicker.
- `date-time` Datepicker with time options.
- `toggle` Checkbox.
- `entity-list-field` Allows creation of multiple child entities. Useful repeated content structures like slider slides. See [Setting up an Entity List Field](#setting-up-an-entity-list-field)
- `entity-list-foreign` This is required the `entity-list-field` type to work. It's what stores the parent entity of the entities in the list. Clicking to add one will automatically save the field so you don't have to worry about what values it needs. See [Setting up an Entity List Field](#setting-up-an-entity-list-field)
- `belongs-to-type` This field allows you to link a type of entity to a template. When you add the field to a template it will give you dropdowns to select the type, and optionally template, that you want to link to the template you're adding the field to. This allows you to access all of the entities of the selected type from the view.
- `belongs-to-entity` Allows you to link one entity to another. When adding the field to a template you will be given the option to limit the available entities to a specific type or template.
- `belongs-to-many-entities` Same as `belongs-to-entity` but allows you to pick many entities rather than just one.
- `hidden` Allows you to add a hidden field to the create/edit form of entities that will save the value entered when the field is created. 

## Entities

## Values

---

# Setting up an Entity List Field

