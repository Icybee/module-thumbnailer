Brickrouge.Widget.AdjustThumbnailOptions = (function() {

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
}) ();