# The "Thumbnailer" module (thumbnailer)

Creates thumbnails from images and managed images using options or configured versions.

The module extends the _Image_ active record with the `thumbnail()` method and the `thumbnail`
lazy getter, and provides an interface to configure and manage its cache that integrates with the
unified cache system of the "Cache" module (cache).





### Requirements

This module is for the CMS [Icybee](http://icybee.org/).






## Installation

The recommended way to install this package is through [composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
	"minimum-stability": "dev",
	"require": {
		"icybee/module-thumbnailer": "*"
	}
}
```





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/module-thumbnailer), its repository can
be cloned with the following command line:

	$ git clone git://github.com/Icybee/module-thumbnailer.git thumbnailer
	




## Event hooks

### `ICanBoogie\Modules\System\Cache\Collection::alter`

Adds our cache manager to the cache collection.





### `Icybee\ConfigBlock::alter_children`

Adds a _thumbnails_ section to the config block of modules defining thumbnail versions using the
"thumbnails" config.





### `Icybee\ConfigOperation::properties:before`

Pre-parses defined thumbnail versions before the config is saved.





## Prototype methods

### `Icybee\Modules\Images\Image::thumbnail`

Adds the `thumbnail()` method to the _Image_ active record.

```php
<?php

echo $core->models['images']->one->thumbnail('my-version-name');
echo $core->models['images']->one->thumbnail('w:64;h:64;m:fit');
```





### `Icybee\Modules\Images\Image::get_thumbnail`

Adds the `thumbnail` lazy getter for the `primary` thumbnail version.





## License

This module is licensed under the New BSD License - See the LICENSE file for details.