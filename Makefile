# customization

PACKAGE_NAME = "ICanBoogie/Modules/Thumbnailer"
PACKAGE_VERSION = 3.0.0

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

test: vendor node_modules testphp testjs

testphp:
	@phpunit

testjs:
	@node_modules/mocha/bin/mocha tests/*.js

vendor:
	@composer install

node_modules:
	sudo npm install mocha chai mootools

update:
	@composer update

autoload:
	@composer dump-autoload

doc: vendor
	@mkdir -p "docs"

	@apigen \
	--source ./ \
	--destination docs/ --title $(PACKAGE_NAME) \
	--exclude "*/tests/*" \
	--exclude "*/composer/*" \
	--template-config /usr/share/php/data/ApiGen/templates/bootstrap/config.neon

clean:
	@rm -f .README.md.html
	@rm -fR docs
	@rm -fR vendor
	@rm -f composer.lock
	@rm -f composer.phar
	@rm -f public/module-uncompressed.css
	@rm -f public/module-uncompressed.js
	@rm -Rf tests/repository/thumbnailer
	@rm -f tests/repository/vars/cached_thumbnailer_versions
