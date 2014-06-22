# Thumbnailer [![Build Status](https://travis-ci.org/Icybee/module-thumbnailer.png?branch=2.1)](https://travis-ci.org/Icybee/module-thumbnailer)

The Thumbnailer module (`thumbnailer`) creates thumbnails from images and managed
images using options or configured versions.

The module extends the _Image_ active record with the `thumbnail()` method and the `thumbnail`
lazy getter, and provides an interface to configure and manage its cache that integrates with the
unified cache system of the "Cache" module (cache). The module also extends the _core_ object
with the `thumbnail_versions` lazy getter.

```php
<?php

namespace ICanBoogie\Modules\Thumbnailer;

$versions = $core->thumbnailer_versions;
$versions['popover'] = [ 'width' => 420, 'height' => 340 ];
# or
$versions['popover'] = 'w:420;h:340';
# or
$versions['popover'] = '{"w":"420","h":"340"}';
# or 
$versions['popover'] = '420x340';

$thumbnail = new Thumbnail('/images/madonna.jpeg', 'popover');

echo $thumbnail;      // <img src="/api/thumbnail/420x340/fill?s=%2Fimages%2Fmadonna.jpeg&amp;v=popover" alt="" width="420" height="340" class="thumbnail thumbnail--popover" />
echo $thumbnail->url; // /api/thumbnail/420x340/fill?s=%2Fimages%2Fmadonna.jpeg&v=popover

$thumbnail = new Thumbnail('/images/madonna.jpeg', '64x64.png');

echo $thumbnail;      // <img src="/api/thumbnail/64x64/fill.png&amp;s=%2Fimages%2Fmadonna.jpeg" alt="" width="64" height="64" class="thumbnail" />
echo $thumbnail->url; // /api/thumbnail/64x64/fill.png&s=%2Fimages%2Fmadonna.jpeg
```





## Event hooks





### `ICanBoogie\Modules\System\Cache\Collection::alter`

Adds our cache manager to the cache collection.





### `Icybee\ConfigBlock::alter_children`

Adds a _thumbnails_ section to the config block of modules defining thumbnail versions using the
"thumbnails" config.





### `Icybee\ConfigOperation::properties:before`

Pre-parses defined thumbnail versions before the config is saved.





## Prototype methods





### `ICanBoogie\Core\get_thumbnail_versions`

Adds the `thumbnail_versions` lazy getter to the _core_ object. The getter returns a version
collection configured with the versions saved in the registry. Third parties may alter this
collection with an event hook attached to the `ICanBoogie\Modules\Thumbnailer\Versions::alter`
event.






----------





## Requirements

The package requires PHP 5.4 or later.





## Installation

The recommended way to install this package is through [Composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
	"minimum-stability": "dev",
	"require": {
		"icybee/module-thumbnailer": "2.1.x"
	}
}
```





### Cloning the repository

The package is [available on GitHub](https://github.com/Icybee/module-thumbnailer), its repository can
be cloned with the following command line:

	$ git clone git://github.com/Icybee/module-thumbnailer.git thumbnailer





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all the dependencies required to run the suite. The package
directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://travis-ci.org/Icybee/module-thumbnailer.png?branch=2.1)](https://travis-ci.org/Icybee/module-thumbnailer)





## Documentation

The package is documented as part of the [Icybee](http://icybee.org/) CMS
[documentation](http://icybee.org/docs/). The documentation for the package and its
dependencies can be generated with the `make doc` command. The documentation is generated in
the `docs` directory using [ApiGen](http://apigen.org/). The package directory can later by
cleaned with the `make clean` command.





## License

This module is licensed under the New BSD License - See the [LICENSE](LICENSE) file for details.