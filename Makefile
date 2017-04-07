# customization

PACKAGE_NAME = icanboogie/module-thumbnailer
PACKAGE_VERSION = 4.0
PHPUNIT_VERSION = phpunit-5.7.phar
PHPUNIT_FILENAME = build/$(PHPUNIT_VERSION)
PHPUNIT = php $(PHPUNIT_FILENAME)

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

JS_COMPRESSOR = `which uglifyjs` $^ \
	--compress \
	--mangle \
	--screw-ie8 \
	--source-map $@.map
#JS_COMPRESSOR = cat $^ # uncomment this line to produce an uncompressed file
JS_COMPRESSED = public/module.js
JS_UNCOMPRESSED = public/module-uncompressed.js

CSS_COMPRESSOR = curl -X POST -s --data-urlencode 'input@$^' http://cssminifier.com/raw
#CSS_COMPRESSOR = cat $^ # uncomment this line to produce an uncompressed file
CSS_COMPRESSED = public/module.css
CSS_UNCOMPRESSED = public/module-uncompressed.css

all: vendor node_modules $(PHPUNIT_FILENAME) $(JS_COMPRESSED) $(JS_UNCOMPRESSED) $(CSS_COMPRESSED) $(CSS_UNCOMPRESSED)

$(JS_COMPRESSED): $(JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) >$@

$(JS_UNCOMPRESSED): $(JS_FILES)
	cat $^ >$@

$(CSS_COMPRESSED): $(CSS_UNCOMPRESSED)
	$(CSS_COMPRESSOR) >$@

$(CSS_UNCOMPRESSED): $(CSS_FILES)
	cat $^ >$@

vendor:
	@COMPOSER_ROOT_VERSION=$(PACKAGE_VERSION) composer install

update:
	@COMPOSER_ROOT_VERSION=$(PACKAGE_VERSION) composer update

autoload: vendor
	@composer dump-autoload

test-dependencies: vendor $(PHPUNIT_FILENAME)

$(PHPUNIT_FILENAME):
	mkdir -p build
	wget https://phar.phpunit.de/$(PHPUNIT_VERSION) -O $(PHPUNIT_FILENAME)

test: test-dependencies
	@$(PHPUNIT)

test-coverage: test-dependencies
	@mkdir -p build/coverage
	@$(PHPUNIT) --coverage-html ../build/coverage

test-coveralls: test-dependencies
	@mkdir -p build/logs
	COMPOSER_ROOT_VERSION=$(PACKAGE_VERSION) composer require satooshi/php-coveralls
	@$(PHPUNIT) --coverage-clover ../build/logs/clover.xml
	php vendor/bin/coveralls -v

test-js: node_modules
	@node_modules/mocha/bin/mocha tests/*.js tests/lib/*.js

node_modules:
	npm install mocha mootools chai

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
	@rm -Rf tests/repository/var

.PHONY: all autoload doc clean test test-coverage test-coveralls update
