# Jam Changelog

## Version 1.11.0 (Future)
- Add entity archive

## Version 1.10.1 (Future)
- Remove placeholder from expiry date field

## Version 1.10.0 (2017-08-29)
- Add ability to publish expired entity list items from listing
- Don't clear entity expired_at dates in the future

## Version 1.9.0 (2017-08-23)
- Make date fields return carbon object
- Add `loadRelationFields` method
- Add abstract tags field
- Add entity duplication functionality 
- Add orWhereValue and leftJoinValueByKey scopes to Entity
- Allow search query to be modified in EntityDecorator
- Add scheduled expiry dates for entities

## Version 1.8.1 (2017-07-19)
- Fix template creation from command line

## Version 1.8.0 (2017-07-19)
- Add preview link to entity edit form
- Make old path redirect 301
- Add `whereNotBelongsTo` scope to Entity

## Version 1.7.0 (2017-06-26)
- Add ability to change type of existing fields
- Add "Save and Create Another" option to entity form
- Add link field type

## Version 1.6.1 (2017-06-19)
- Fix per page option on entity listing

## Version 1.6.0 (2017-06-12)
- Add seeder generator for copying template schema to production
- Add template history tracking for easier migration of JAM schema
- Allow entities to be saved as draft even if the form doesn't validate
- Allow custom labels to be added to JAM fields
- Automatically insert a blank value to the current revision when new field added to a template

## Version 1.5.1 (2017-05-25)
- Add optional format argument to published_at mutator on entity

## Version 1.5.0 (2017-05-04)
- Add search filter to entity report
- Throw different exception in link builder when running in console
- Don't parse oembed value if it's null in oembed field type
- Fix template duplication (don't duplicate meta fields)
- Add `active()` and `ordered()` to `forType` query in repo
- Show type name in jam template listing
- Add accessor method for pulished date in entity
- Add type menu title to type listing for better sparation
- Allow BelongsTo field to be extended 
- Allow operator to be changed for `whereValue` entity scope
- Change EntityPath fillable to guarded
- Move history link to published dropdown on entity report

## Version 1.4.1 (2017-03-14)
- Remove debug

## Version 1.4.0 (2017-03-14)

-   Allow entity types to be gated

## Version 1.3.2 (2017-03-13)

-   Fix empty textarea stripping in value model

## Version 1.3.1 (2017-02-23)

-   Make entity role restriction togglable and off by default

## Version 1.3.0 (2017-02-13)

-   Allow nested entities to create child entities from parent row in report

## Version 1.2.5 (2017-02-10)

-   Fix search breadcrumbs
-   Allow name/path to be overridden for indexed entities

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
