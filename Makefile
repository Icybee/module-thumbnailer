# customization

PACKAGE_NAME = icanboogie/module-thumbnailer
PACKAGE_VERSION = 3.0.0
COMPOSER_ENV = COMPOSER_ROOT_VERSION=$(PACKAGE_VERSION)

# do not edit the following lines

# assets

JS_FILES = \
	lib/Module.js \
	lib/AdjustThumbnailOptions.js \
	lib/AdjustThumbnailVersion.js \
	lib/PopThumbnailVersion.js

CSS_FILES = \
	lib/AdjustThumbnailOptions.css \
	lib/AdjustThumbnailVersion.css

JS_COMPRESSOR = curl -X POST -s --data-urlencode 'input@$^' http://javascript-minifier.com/raw
#JS_COMPRESSOR = cat $^ # uncomment this line to produce an uncompressed file
JS_COMPRESSED = public/module.js
JS_UNCOMPRESSED = public/module-uncompressed.js

CSS_COMPRESSOR = curl -X POST -s --data-urlencode 'input@$^' http://cssminifier.com/raw
#CSS_COMPRESSOR = cat $^ # uncomment this line to produce an uncompressed file
CSS_COMPRESSED = public/module.css
CSS_UNCOMPRESSED = public/module-uncompressed.css

all: $(JS_COMPRESSED) $(JS_UNCOMPRESSED) $(CSS_COMPRESSED) $(CSS_UNCOMPRESSED)

$(JS_COMPRESSED): $(JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) >$@

$(JS_UNCOMPRESSED): $(JS_FILES)
	cat $^ >$@

$(CSS_COMPRESSED): $(CSS_UNCOMPRESSED)
	$(CSS_COMPRESSOR) >$@

$(CSS_UNCOMPRESSED): $(CSS_FILES)
	cat $^ >$@

test: vendor node_modules test-php test-js

test-coverage: vendor
	@mkdir -p build/coverage
	@phpunit --coverage-html build/coverage

test-php:
	@phpunit

test-js:
	@node_modules/mocha/bin/mocha tests/*.js

vendor:
	@$(COMPOSER_ENV) composer install

node_modules:
	@npm install mocha chai mootools

update:
	@$(COMPOSER_ENV) composer update

autoload: vendor
	@$(COMPOSER_ENV) composer dump-autoload

doc: vendor
	@mkdir -p build/docs
	@apigen generate \
	--source lib \
	--destination build/docs/ \
	--title "$(PACKAGE_NAME) v$(PACKAGE_VERSION)" \
	--template-theme "bootstrap"

clean:
	@rm -fR build
	@rm -fR vendor
	@rm -fR node_modules
	@rm  -f composer.lock
	@rm  -f public/module-uncompressed.css
	@rm  -f public/module-uncompressed.js
	@rm -Rf tests/repository/thumbnailer
	@rm -f tests/repository/vars/cached_thumbnailer_versions
