# Jam Package

## Installation

1. Require the package in Composer, by running `composer require bozboz/jam`
2. Add `Bozboz\Jam\Providers\JamServiceProvider::class` to the providers array in
   config/app.php
3. Run `php artisan vendor:publish && php artisan migrate` 

## Data Setup

### Types

Entity types are the top level of the jam schema. They can essentially be thought of as models. Types are defined in 
service providers. Jam comes with a "Pages" type out of the box which is registered in the registerEntityTypes method
of JamServiceProvider.

When you register a type you can give it a report, link builder, menu builder and entity.

-   `report` Admin report class for listing. Use NestedReport to enable nested sorting. Default `Bozboz\Admin\Reports\Report`
-   `link_builder` Simple class for creating paths and urls to entities. Default `Bozboz\Jam\Entities\LinksDisabled`
-   `menu_builder` Handles where to put the type in the admin menu. Default `Bozboz\Jam\Types\Menu\Content`
    -   `Hidden` don't display in menu
    -   `Content` display in content dropdown
    -   `Standalone` 
-   `entity` Actual entity class to be used. Default `Bozboz\Jam\Entities\Entity`

## Templates

Once you've got some types you need to give them some templates. A template dictates what data the entity has. Types can have as many templates as you like. To add a template log in to the admin, click on "Jam" then pick a type to edit. When adding a template you must give it a name but the view, listing view and listing fields are optional. 
