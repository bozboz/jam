# Jam Changelog

## Version 1.1.0 (Future)

-   Rearrange publishing/scheduling feature for better usability
-   Allow draft revisions to be created on published entities
-   Allow previewing unpublished entities/draft revisions

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
