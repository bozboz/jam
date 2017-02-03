# Jam Changelog

## Version 1.2.4 (2017-02-03)

-   Also gate entities that have gated ancestors
-   Fix sorting on duplicated template fields

## Version 1.2.3 (2017-01-31)

-   Remove stupid debug stupid me shouldn't have committed

## Version 1.2.2 (2017-01-31)

-   Fix duplicate entity path form error

## Version 1.2.1 (2017-01-26)

-   Make the slug field a little more user friendly
-   Prevent meta fields from being duplicated when duplicating a template

## Version 1.2.0 (2017-01-26)

-   Allow entities to be gated by user role
-   Strip the stupid `<p><br></p>` the summernote adds out of WYSIWYG fields
-   Set up dropdown field type

## Version 1.1.2 (2017-01-26)

-   Prevent meta fields from being duplicated when duplicating a template

## Version 1.1.1 (2017-01-25)

-   Fix initial publish action

## Version 1.1.0 (2017-01-09)

-   Rearrange publishing/scheduling feature for better usability
-   Allow draft revisions to be created on published entities
-   Allow previewing unpublished entities/draft revisions
-   Add revision diff feature
-   Add validation to fields to prevent duplicate names on templates

## Version 1.0.6 (2017-01-26)

-   Prevent meta fields from being duplicated when duplicating a template

## Version 1.0.5 (2017-01-09)

-   Fix value duplication for belongs to fields

## Version 1.0.4 (2016-12-23)

-   Exclude inactive entities from BelongsToMany field query

## Version 1.0.3 (2016-12-22)

-   Make repo in front end controller protected rather than private
-   Fix user field default value and make defaulting to logged in user optional
-   Automatically inject values on new collection of entities if values have been eager loaded 

## Version 1.0.2 (2016-12-21)

-   Fix nested entity list redirect 

## Version 1.0.1 (2016-12-16)

-   Fix entity list save redirect for nested lists

## Version 1.0.0 (2016-12-16)

-   Add max_uses to templates
-   Don't make slugs unique
-   Throw an exception when saving an entity with a non-unique path
-   Soft delete paths for soft deleted entities
