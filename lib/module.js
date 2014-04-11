ICanBoogie.Modules.Thumbnailer = (function() {

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

		this.assertSize = function(w, h, m)
		{
			switch (m)
			{
				case this.RESIZE_FIXED_WIDTH:
				{
					if (!w)
					{
						throw new Error('Width is required for the ' + m + ' resize method.')
					}
				}
				break

				case this.RESIZE_FIXED_HEIGHT:
				{
					if (!h)
					{
						throw new Error('Height is required for the ' + m + ' resize method.')
					}
				}
				break

				default:
				{
					if (!w || !h)
					{
						throw new Error('Both width and height are required for the ' + m + ' resize method.')
					}
				}
				break
			}

			return true
		}
	}

	var Version= new function() {

		this.defaults = {

			'background': null,
			'default': null,
			'format': null,
			'filter': null,
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

		this.shorthands = {

			b: 'background',
			d: 'default',
			f: 'format',
			ft: 'filter',
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

		this.normalize = function(options) {

			var defaults = this.defaults
			, keys = Object.keys(defaults)
			, shorthands = this.shorthands
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

			return Object.filter(normalized, function(value, key) {

				return Object.contains(keys, key)

			})
		}

		this.filter = function(options) {

			var defaults = this.defaults
			, keys = Object.keys(defaults)
			, normalized = this.normalize(options)

			return Object.filter(normalized, function(value, key) {

				return value && value != defaults[key]

			})
		}
	}

	return {

		Image: Image,

		Thumbnail: new Class({

			options: {

			},

			initialize: function(src, options) {

				this.src = src
				this.options = options
			},

			toString: function()
			{
				var options = Version.normalize(this.options)
				, url = this.src
				, w = options.width
				, h = options.height
				, m = options.method
				, q = null

				Image.assertSize(w, h, m)

				var capture = url.match(/repository\/files\/image\/(\d+)/)

				if (capture.length)
				{
					url = '/api/images/' + capture[1]
				}

				url += '/' + (w || '') + 'x' + (h || '')

				if (m)
				{
					url += '/' + m
				}

				delete options.width
				delete options.height
				delete options.method

				q = Object.toQueryString(Version.filter(options))

				if (q)
				{
					url += '?' + q
				}

				return url
			}

		}),

		Version: Version
	}

})();