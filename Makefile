# customization

PACKAGE_NAME = "ICanBoogie/Modules/Thumbnailer"

# do not edit the following lines

# assets

JS_FILES = \
	lib/module.js \
	lib/elements/adjust-thumbnail-options.js \
	lib/elements/adjust-thumbnail-version.js \
	lib/elements/pop-thumbnail-version.js

CSS_FILES = \
	lib/elements/adjust-thumbnail-options.css \
	lib/elements/adjust-thumbnail-version.css

JS_COMPRESSOR = curl -X POST -s --data-urlencode 'js_code@$^' --data-urlencode 'utf8=1' http://marijnhaverbeke.nl/uglifyjs
#JS_COMPRESSOR = cat $^
JS_COMPRESSED = public/module.js
JS_UNCOMPRESSED = public/module-uncompressed.js

CSS_COMPRESSOR = curl -X POST -s --data-urlencode 'input@$^' http://cssminifier.com/raw
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

test: vendor
	@phpunit

vendor: composer.phar
	@php composer.phar install --prefer-source --dev

composer.phar:
	@echo "Installing composer..."
	@curl -s https://getcomposer.org/installer | php

update:
	@php composer.phar update --prefer-source --dev

doc: vendor
	@mkdir -p "docs"

	@apigen \
	--source ./ \
	--destination docs/ --title $(PACKAGE_NAME) \
	--exclude "*/tests/*" \
	--exclude "*/composer/*" \
	--template-config /usr/share/php/data/ApiGen/templates/bootstrap/config.neon

clean:
	@rm -fR docs
	@rm -fR vendor
	@rm -f composer.lock
	@rm -f composer.phar
