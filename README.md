The "Thumbnailer" module (thumbnailer)
======================================

Creates thumbnails from images and managed images using options or configured versions.

The module extends the _Image_ active record with the `thumbnail()` method and the `thumbnail`
lazy getter, and provides an interface to configure and manage its cache that integrates with the
unified cache system of the "Cache" module (cache).




Event hook: ICanBoogie\Modules\System\Cache\Collection::alter
-------------------------------------------------------------

Adds our cache manager to the cache collection.




Event hook: Icybee\ConfigBlock::alter_children
----------------------------------------------

Adds a _thumbnails_ section to the config block of modules defining thumbnail versions using the
"thumbnails" config.




Event hook: Icybee\ConfigOperation::properties:before
-----------------------------------------------------

Pre-parses defined thumbnail versions before the config is saved.




Prototype method: ICanBoogie\ActiveRecord\Image::thumbnail
----------------------------------------------------------

Adds the `thumbnail()` method to the _Image_ active record.




Prototype method: ICanBoogie\ActiveRecord\Image::get_thumbnail
--------------------------------------------------------------

Adds the `thumbnail` lazy getter for the `primary` thumbnail version.