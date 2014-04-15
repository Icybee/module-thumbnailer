Brickrouge.Widget.AdjustThumbnailVersion = new Class
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
});