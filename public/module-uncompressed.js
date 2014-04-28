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

	var Version = new function() {

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

	var Thumbnail = new Class({

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

	})

	ICanBoogie.Modules.Thumbnailer = {

		Image: Image,
		Thumbnail: Thumbnail,
		Version: Version

	}

} ();Brickrouge.Widget.AdjustThumbnailOptions = (function() {

	var Version = ICanBoogie.Modules.Thumbnailer.Version

	Version.defaults.lightbox = null
	Version.shorthands.lb = 'lightbox'

	return new Class({

		Implements: [ Events ],

		initialize: function(el)
		{
			this.element = el = document.id(el)
			this.controls = {}

			Object.each(Version.defaults, function(value, key) {

				var control = el.getElement('[name="' + key + '"]')

				if (!control) return

				this[key] = control

			}, this.controls)

			el.addEvent('change', this.onChange.bind(this))

			this.checkMethod()
			this.checkQuality()
		},

		checkMethod: function()
		{
			var h = this.controls.height
			, w = this.controls.width

			switch (this.controls.method.get('value'))
			{
				case 'fixed-height':
				{
					h.readOnly = false
					w.readOnly = true
				}
				break

				case 'fixed-width':
				{
					h.readOnly = true
					w.readOnly = false
				}
				break

				default:
				{
					w.readOnly = false
					h.readOnly = false
				}
				break
			}
		},

		checkQuality: function()
		{
			var value = this.controls.format.get('value')

			this.controls.quality.getParent().setStyle('display', (value != 'jpeg') ? 'none' : '')
		},

		getValue: function()
		{
			var values = this.element.toQueryString().parseQueryString()

			if (this.controls.width.readOnly)
			{
				delete values.width
			}

			if (this.controls.height.readOnly)
			{
				delete values.height
			}

			return values
		},

		setValue: function(values)
		{
			Object.each(Version.normalize(values), function(value, key) {

				var control = this[key]

				if (!control) return

				if (control.type == 'checkbox')
				{
					control.set('checked', !!value)
				}
				else
				{
					control.set('value', value)
				}
			}, this.controls)

			this.checkMethod()
			this.checkQuality()
		},

		onChange: function(ev)
		{
			this.checkMethod()
			this.checkQuality()

			this.fireEvent('change', { target: this, values: this.getValue() })
		}
	})
}) ();Brickrouge.Widget.AdjustThumbnailVersion = new Class
({
	Implements: [ Options, Events ],

	initialize: function(el, options)
	{
		this.element = el = $(el);

		var width = el.getElement('input[name="width"]') || el.getElement('input[name$="[width]"]')
		, height = el.getElement('input[name="height"]') || el.getElement('input[name$="[height]"]')
		, method = el.getElement('select[name="method"]') || el.getElement('select[name$="[method]"]')
		, format = el.getElement('select[name="format"]') || el.getElement('select[name$="[format]"]')
		, quality = el.getElement('input[name="quality"]') || el.getElement('input[name$="[quality]"]')

		this.elements =
		{
			width: width,
			height: height,
			method: method,
			format: format,
			quality: quality,
			'no-upscale': el.getElement('input[name="no-upscale"]') || el.getElement('input[name$="[no-upscale]"]'),
			background: el.getElement('input[name="background"]') || el.getElement('input[name$="[background]"]'),
			filter: el.getElement('input[name="filter"]') || el.getElement('input[name$="[filter]"]')
		}

		Object.each(this.elements, function(control) {

			if (!control) return

			control.addEvent('change', this.fireChange.bind(this))

		}, this)

		function checkMethod()
		{
			switch (method.get('value'))
			{
				case 'fixed-height':

					height.readOnly = false
					width.readOnly = true

					break;

				case 'fixed-width':

					height.readOnly = true
					width.readOnly = false

					break

				default:

					height.readOnly = false
					width.readOnly = false

					break
			}
		}

		function checkQuality()
		{
			var value = format.get('value')

			quality.getParent().setStyle('display', (value != 'jpeg') ? 'none' : '')
		}

		checkMethod()
		checkQuality()

		method.addEvent('change', checkMethod)
		format.addEvent('change', checkQuality)
	},

	fireChange: function()
	{
//		console.log(this, this.element.toQueryString().parseQueryString())
	},

	setValue: function(value)
	{
		if (typeOf(value) == 'string')
		{
			value = JSON.decode(value)
		}

		if (!value)
		{
			return
		}

		Object.each(value, function(value, key) {

			if (!this.elements[key])
			{
				return
			}

			if (key == 'no-upscale' || key == 'interlace')
			{
				this.elements[key].set('checked', value)
			}
			else
			{
				this.elements[key].set('value', value)
			}

		}, this )
	},

	getValue: function()
	{
		var value = this.element.toQueryString().parseQueryString()

		value = ICanBoogie.Modules.Thumbnailer.Version.normalize(value)

		if (!value.width && !value.height)
		{
			return
		}

		value = Object.filter(value, function(value, key) {

			return !!value

		})

		return JSON.encode(value)
	}
});Brickrouge.Widget.PopThumbnailVersion = new Class
({
	Extends: Brickrouge.Widget.Spinner,

	initialize: function(el, options)
	{
		this.parent(el, options)

		this.control = this.element.getElement('input')
	},

	open: function()
	{
		this.resetValue = this.getValue()

		if (this.popover)
		{
			this.popover.adjust.setValue(this.resetValue)
			this.popover.show()
		}
		else
		{
			new Request.Widget('adjust-thumbnail-version/popup', function(widget) {

				this.attachAdjust(widget);

				this.popover.show();

				/*
				 * The adjust object is available after the `brickrouge.construct` event has been
				 * fired. The event is fired when the popover is opened.
				 */

				//this.popover.adjust.addEvent('change', this.change.bind(this));
				this.popover.addEvent('action', this.onAction.bind(this));

			}.bind(this)).get({ value: this.getValue() })
		}
	},

	encodeValue: function(value)
	{
		if (value && typeOf(value) == 'object')
		{
			value = JSON.encode(value)
		}

		return value
	},

	decodeValue: function(value)
	{
		if (typeOf(value) == 'string')
		{
			try
			{
				return JSON.decode(value)
			}
			catch (e) { }
		}

		return value
	},

	formatValue: function(value)
	{
		if (!value)
		{
			return ''
		}

		value = this.decodeValue(value)
		value = ICanBoogie.Modules.Thumbnailer.Version.normalize(value)

		return ''
		+ (value.width || '<em>auto</em>')
		+ '×'
		+ (value.height || '<em>auto</em>')
		+ ' '
		+ value.method
		+ ' .'
		+ (value.format || '<em>auto</em>')
	},

	attachAdjust: function(adjust)
	{
		this.popover = new Icybee.Widget.AdjustPopover(adjust, { anchor: this.element })
	},

	change: function(ev)
	{
//		console.log('change: ', ev);
	},

	onAction: function(ev)
	{
		switch (ev.action)
		{
			case 'use':
			{
				this.setValue(ev.popover.adjust.getValue())
			}
			break

			case 'remove':
			{
				this.setValue(null)
			}
			break

			case 'cancel':
			{
				this.setValue(this.resetValue)
			}
			break
		}

		this.popover.hide()
	}
});