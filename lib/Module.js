var module

define('icybee/thumbnailer/version', [

], function () {

	function extract_options_from_uri(uri)
	{
		let options = {}
		let path = uri
		let queryStringPosition = uri.indexOf('?')

		if (queryStringPosition > -1)
		{
			path = uri.substring(0, queryStringPosition)
			let queryString = uri.substring(queryStringPosition + 1)
			options = String.parseQueryString(queryString)
			options = Version.widen(options)
		}

		options = Object.merge({

			width: null,
			height: null,
			method: null,
			format: null

		}, options)

		const matches = path.match(/\/?(\d+x\d+|\d+x|x\d+)(\/([^\/\.]+))?(\.([a-z]+))?/)

		if (matches)
		{
			const size = matches[1].split('x')
			const w = size[0]
			const h = size[1]

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

	const OPTIONS_DEFAULTS = {

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

	const OPTIONS_DEFAULTS_SHORTENED = {

		'b': null,
		'd': null,
		'fi': null,
		'f': null,
		'h': null,
		'm': 'fill',
		'ni': false,
		'nu': false,
		'o': null,
		'p': null,
		'q': 90,
		's': null,
		'w': null
	}

	const OPTIONS_SHORTHANDS = {

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

	const Version = class {

		/**
		 * @param {OPTIONS_DEFAULTS} options
		 */
		constructor(options)
		{
			this.options = Object.assign({}, OPTIONS_DEFAULTS, this.widen(options))
		}

		toString()
		{
			return this.serialize(this.options)
		}

		/**
		 * @returns {string}
		 */
		get background()
		{
			return this.options.background
		}

		/**
		 * @param {string} background
		 */
		set background(background)
		{
			this.options.background = background
		}

		/**
		 * @returns {string}
		 */
		get default()
		{
			return this.options.default
		}

		/**
		 * @param {string} value
		 */
		set default(value)
		{
			this.options.default = value
		}

		/**
		 * @returns {string}
		 */
		get filter()
		{
			return this.options.filter
		}

		/**
		 * @param {string} filter
		 */
		set filter(filter)
		{
			this.options.filter = filter
		}

		/**
		 * @returns {string}
		 */
		get format()
		{
			return this.options.format
		}

		/**
		 * @param {string} format
		 */
		set format(format)
		{
			this.options.format = format
		}

		/**
		 * @returns {number}
		 */
		get height()
		{
			return this.options.height
		}

		/**
		 * @param {number} height
		 */
		set height(height)
		{
			this.options.height = height
		}

		/**
		 * @returns {string}
		 */
		get method()
		{
			return this.options.method
		}

		/**
		 * @param {string} method
		 */
		set method(method)
		{
			this.options.method = method
		}

		/**
		 * @returns {boolean}
		 */
		get noInterlace()
		{
			return this.options.noInterlace
		}

		/**
		 * @param {boolean} noInterlace
		 */
		set noInterlace(noInterlace)
		{
			this.options.noInterlace = noInterlace
		}

		/**
		 * @returns {boolean}
		 */
		get noUpscale()
		{
			return this.options.noUpscale
		}

		/**
		 * @param {boolean} noUpscale
		 */
		set noUpscale(noUpscale)
		{
			this.options.noUpscale = noUpscale
		}

		/**
		 * @returns {string}
		 */
		get overlay()
		{
			return this.options.overlay
		}

		/**
		 * @param {string} overlay
		 */
		set overlay(overlay)
		{
			this.options.overlay = overlay
		}

		/**
		 * @returns {string}
		 */
		get path()
		{
			return this.options.path
		}

		/**
		 * @param {string} path
		 */
		set path(path)
		{
			this.options.path = path
		}

		/**
		 * @returns {number}
		 */
		get quality()
		{
			return this.options.quality
		}

		/**
		 * @param {number} quality
		 */
		set quality(quality)
		{
			this.options.quality = quality
		}

		/**
		 * @returns {string}
		 */
		get src()
		{
			return this.options.src
		}

		/**
		 * @param {string} src
		 */
		set src(src)
		{
			this.options.src = src
		}

		/**
		 * @returns {string}
		 */
		get version()
		{
			return this.options.version
		}

		/**
		 * @param {string} version
		 */
		set version(version)
		{
			this.options.version = version
		}

		/**
		 * @returns {number}
		 */
		get width()
		{
			return this.options.width
		}

		/**
		 * @param {number} width
		 */
		set width(width)
		{
			this.options.width = width
		}

		/**
		 * Widens option names.
		 *
		 * Note: Extraneous options are not filtered.
		 *
		 * @param {OPTIONS_DEFAULTS} options
		 *
		 * @return {OPTIONS_DEFAULTS}
		 */
		static widen(options) {

			const rc = {}

			Object.each(options, (value, name) => {

				if (name in OPTIONS_SHORTHANDS)
				{
					name = OPTIONS_SHORTHANDS[name]
				}

				rc[name] = value

			})

			return rc

		}

		/**
		 * Shortens option names.
		 *
		 * @param {OPTIONS_DEFAULTS} options
		 *
		 * @returns {OPTIONS_DEFAULTS_SHORTENED}
		 */
		static shorten(options) {

			const rc = {}

			Object.each(options, (value, name) => {

				var key = Object.keyOf(OPTIONS_SHORTHANDS, name)

				if (key)
				{
					name = key
				}

				rc[name] = value

			})

			return rc

		}

		/**
		 * Normalizes options.
		 *
		 * @param {object} options
		 *
		 * @returns {OPTIONS_DEFAULTS}
		 */
		static normalize(options) {

			const defaults = OPTIONS_DEFAULTS
			const keys = Object.keys(defaults)
			const shorthands = OPTIONS_SHORTHANDS
			const normalized = Object.clone(defaults)

			Object.each(options, (value, key) => {

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

			let m = 'method' in normalized ? normalized['method'] : null
			let w = 'width' in normalized ? normalized['width'] : null
			let h = 'height' in normalized ? normalized['height'] : null

			if (w && h && !m) m = 'fill'
			else if (w && !h) m = 'fixed-width'
			else if (!w && h) m = 'fixed-height'

			if (m) normalized['method'] = m
			else delete normalized['method']

			return Object.filter(normalized, (value, key) => {

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
		 * @param {object} options
		 *
		 * @returns {OPTIONS_DEFAULTS} The filtered thumbnail options.
		 */
		static filter(options) {

			const defaults = OPTIONS_DEFAULTS
			const normalized = this.normalize(options)
			const w = 'width' in normalized ? normalized['width'] : null
			const h = 'height' in normalized ? normalized['height'] : null
			const m = 'method' in normalized ? normalized['method'] : null

			if ((w && h && m === 'fill')
				|| (w && !h && m === 'fixed-width')
				|| (!w && h && m === 'fixed-height'))
			{
				delete normalized['method']
			}

			return Object.filter(normalized, (value, key) => {

				return value && value != defaults[key]

			})
		}

		/**
		 * Serialize options.
		 *
		 * @param {OPTIONS_DEFAULTS|OPTIONS_DEFAULTS_SHORTENED} options
		 *
		 * @returns {string}
		 */
		static serialize(options) {

			options = this.filter(options)
			options = this.shorten(options)
			options = Object.merge({ w: "", h: "", m: null, f: null }, options)

			const w = options.w
			const h = options.h
			const m = options.m
			const f = options.f

			let rc = ''

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

			const queryString = Object.toQueryString(options)

			if (queryString) {
				rc += '?' + queryString
			}

			return rc
		}

		/**
		 * Unserialize serialized options.
		 *
		 * @param {string} serialized_options
		 *
		 * @returns {OPTIONS_DEFAULTS}
		 */
		static unserialize(serialized_options)
		{
			let options = extract_options_from_uri(serialized_options)

			return this.filter(options)
		}
	}

	Version.defaults = OPTIONS_DEFAULTS
	Version.shorthands = OPTIONS_SHORTHANDS

	return Version

})

define('icybee/thumbnailer/thumbnail', [

	'icybee/thumbnailer/version'

], function (Version) {

	return class {

		constructor(src, options) {

			this.src = src
			this.options = options
		}

		toString()
		{
			const src = this.src
			const capture = src.match(/repository\/files\/image\/(\d+)/) || src.match(/images\/(\d+|[a-z0-9\-]{36})/)
			let options = this.options
			let url = '/api/thumbnail'

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

	}

})

define('icybee/thumbnailer', [

	'icybee/thumbnailer/version',
	'icybee/thumbnailer/thumbnail'

], function(Version, Thumbnail) {

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

	return ICanBoogie.Modules.Thumbnailer = {

		Image: Image,
		Thumbnail: Thumbnail,
		Version: Version

	}

})
