var module

!function() {

	var Image = new function() {

		this.RESIZE_NONE = 'none'
		this.RESIZE_FIT = 'fit'
		this.RESIZE_FILL = 'fill'
		this.RESIZE_FIXED_HEIGHT = 'fixed-height'
		this.RESIZE_FIXED_HEIGHT_CROPPED = 'fixed-height-cropped'
		this.RESIZE_FIXED_WIDTH = 'fixed-width'
		this.RESIZE_FIXED_WIDTH_CROPPED = 'fixed-width-cropped'
		this.RESIZE_SURFACE = 'surface'
		this.RESIZE_SIMPLE = 'simple'
		this.RESIZE_CONSTRAINED = 'constrained'

		this.assertSize = function(w, h, m) {

			switch (m)
			{
				case this.RESIZE_FIXED_WIDTH:

					if (!w)
					{
						throw new Error('Width is required for the ' + m + ' resize method.')
					}

					break

				case this.RESIZE_FIXED_HEIGHT:

					if (!h)
					{
						throw new Error('Height is required for the ' + m + ' resize method.')
					}

					break

				default:

					if (!w || !h)
					{
						throw new Error('Both width and height are required for the ' + m + ' resize method.')
					}

					break
			}

			return true
		}
	}

	var Version = (function () {

		var OPTIONS_DEFAULTS = {

			'background': null,
			'default': null,
			'filter': null,
			'format': null,
			'height': null,
			'method': 'fill',
			'no-interlace': false,
			'no-upscale': false,
			'overlay': null,
			'path': null,
			'quality': 90,
			'src': null,
			'width': null
		}

		var OPTIONS_SHORTHANDS = {

			b: 'background',
			d: 'default',
			ft: 'filter',
			f: 'format',
			h: 'height',
			m: 'method',
			ni: 'no-interlace',
			nu: 'no-upscale',
			o: 'overlay',
			p: 'path',
			q: 'quality',
			s: 'src',
			v: 'version',
			w: 'width'
		}

		var Version = function(options) {

			this.options = options

		}

		Version.prototype = {

			toString: function()
			{
				return Version.serialize(this.options)
			}

		}

		Version.defaults = OPTIONS_DEFAULTS
		Version.shorthands = OPTIONS_SHORTHANDS

		/**
		 * Widen option names.
		 *
		 * Note: Extraneous options are not filtered.
		 *
		 * @param object options
		 *
		 * @return object
		 */
		Version.widen = function(options) {

			var rc = {}
			, shorthands = OPTIONS_SHORTHANDS

			Object.each(options, function(value, name) {

				if (name in shorthands)
				{
					name = shorthands[name]
				}

				rc[name] = value

			})

			return rc

		}

		Version.shorten = function(options) {

			var rc = {}

			Object.each(options, function(value, name) {

				var key = Object.keyOf(OPTIONS_SHORTHANDS, name)

				if (key)
				{
					name = key
				}

				rc[name] = value

			})

			return rc

		}

		Version.normalize = function(options) {

			var defaults = OPTIONS_DEFAULTS
			, keys = Object.keys(defaults)
			, shorthands = OPTIONS_SHORTHANDS
			, normalized = Object.clone(defaults)

			Object.each(options, function(value, key) {

				if (!value)
				{
					return
				}

				if (shorthands[key])
				{
					key = shorthands[key]
				}

				normalized[key] = value

			})

			var m = 'method' in normalized ? normalized['method'] : null
			, w = 'width' in normalized ? normalized['width'] : null
			, h = 'height' in normalized ? normalized['height'] : null

			if (w && h && !m) m = 'fill'
			else if (w && !h) m = 'fixed-width'
			else if (!w && h) m = 'fixed-height'

			if (m) normalized['method'] = m
			else delete normalized['method']

			return Object.filter(normalized, function(value, key) {

				return Object.contains(keys, key)

			})
		}

		/**
		 * Filter options.
		 *
		 * The options are normalized using {@link normalize()} before they are filtered.
		 * Options than match default values are removed. If `method` equals the implicit resizing
		 * method the option is removed.
		 *
		 * @param object options
		 *
		 * @return object The filtered thumbnail options.
		 */
		Version.filter = function(options) {

			var defaults = OPTIONS_DEFAULTS
			, keys = Object.keys(defaults)
			, normalized = Version.normalize(options)

			, w = 'width' in normalized ? normalized['width'] : null
			, h = 'height' in normalized ? normalized['height'] : null
			, m = 'method' in normalized ? normalized['method'] : null

			if ((w && h && m === 'fill')
			|| (w && !h && m === 'fixed-width')
			|| (!w && h && m === 'fixed-height'))
			{
				delete normalized['method']
			}

			return Object.filter(normalized, function(value, key) {

				return value && value != defaults[key]

			})
		}

		/**
		 * Serialize options.
		 *
		 * @param object options
		 *
		 * @return string
		 */
		Version.serialize = function(options) {

			options = Version.filter(options)
			options = Version.shorten(options)
			options = Object.merge({ w: "", h: "", m: null, f: null }, options)

			var rc = ''
			, w = options.w
			, h = options.h
			, m = options.m
			, f = options.f

			if (w || h)
			{
				rc = w + "x" + h

				if (m) rc += "/" + m
				if (f) rc += "." + f
			}

			delete options['w']
			delete options['h']
			delete options['m']
			delete options['f']

			var queryString = Object.toQueryString(options)

			if (queryString) rc += '?' + queryString

			return rc
		}

		/**
		 * Unserialize serialized options.
		 *
		 * @param string $serialized_options
		 *
		 * @return array
		 */
		Version.unserialize = function(serialized_options)
		{
			options = extract_options_from_uri(serialized_options)
			options = Version.filter(options)

			return options
		}

		function extract_options_from_uri(uri)
		{
			var options = {}
			, path = uri
			, queryStringPosition = uri.indexOf('?')

			if (queryStringPosition > -1)
			{
				path = uri.substring(0, queryStringPosition)
				var queryString = uri.substring(queryStringPosition + 1)
				options = String.parseQueryString(queryString)
				options = Version.widen(options)
			}

			options = Object.merge({

				width: null,
				height: null,
				method: null,
				format: null

			}, options)

			var matches = path.match(/\/?(\d+x\d+|\d+x|x\d+)(\/([^\/\.]+))?(\.([a-z]+))?/)

			if (matches)
			{
				var size = matches[1].split('x')
				, w = size[0]
				, h = size[1]

				if (w)
				{
					options['width'] = parseInt(w)
				}

				if (h)
				{
					options['height'] = parseInt(h)
				}

				if (matches[3])
				{
					options['method'] = matches[3]
				}

				if (matches[5])
				{
					options['format'] = matches[5]
				}

				if (options['format'] && options['format'] == 'jpg')
				{
					options['format'] = 'jpeg'
				}
			}

			return Object.filter(options, function(value, key) {

				return !!value

			})
		}

		return Version

	}) ()

	var Thumbnail = new Class({

		options: {

		},

		initialize: function(src, options) {

			this.src = src
			this.options = options
		},

		toString: function()
		{
			var src = this.src
			, options = this.options
			, capture = src.match(/repository\/files\/image\/(\d+)/) || src.match(/images\/(\d+|[a-z0-9\-]{36})/)
			, url = '/api/thumbnail'

			if (typeOf(options) === 'string')
			{
				options = Version.unserialize(options)
			}

			if (capture)
			{
				url = '/images/' + capture[1]
			}
			else
			{
				options = Object.merge({ src: src }, options)
			}

			return url + '/' + Version.serialize(options)
		}

	});

	var exp = {

		Image: Image,
		Thumbnail: Thumbnail,
		Version: Version

	}

	if (module)
	{
		module.exports = exp
	}
	else
	{
		ICanBoogie.Modules.Thumbnailer = exp
	}

} ();
