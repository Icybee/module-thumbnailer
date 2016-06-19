!function (Brickrouge) {

	"use strict";

	const Version = ICanBoogie.Modules.Thumbnailer.Version

	Version.defaults.lightbox = null
	Version.shorthands.lb = 'lightbox'

	const AdjustThumbnailOptions = new Class({

		Implements: [ Events ],

		initialize: function(el)
		{
			this.element = el
			this.controls = {}

			Object.each(Version.defaults, function(value, key) {

				const control = el.querySelector('[name="' + key + '"]')

				if (!control) return

				this[key] = control

			}, this.controls)

			el.addEvent('change', this.onChange.bind(this))

			this.checkMethod()
			this.checkQuality()
		},

		checkMethod: function()
		{
			const h = this.controls.height
			const w = this.controls.width

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
			const value = this.controls.format.get('value')

			this.controls.quality.setStyle('display', (value != 'jpeg') ? 'none' : '')
			this.controls.quality.previousSibling.setStyle('display', (value != 'jpeg') ? 'none' : '')
		},

		getValue: function()
		{
			const values = this.element.toQueryString().parseQueryString()

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

	Brickrouge.register('AdjustThumbnailOptions', (element, options) => {

		return new AdjustThumbnailOptions(element, options)

	})

} (Brickrouge);
