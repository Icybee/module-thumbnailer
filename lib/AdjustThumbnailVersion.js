define('icybee/thumbnailer/adjust-thumbnail-version', [

	'brickrouge',
	'icybee/adjust',
	'icybee/thumbnailer/version'

],

/**
 * @param {Brickrouge} Brickrouge
 * @param {Icybee.Adjust} Adjust
 * @param {Icybee.Thumbnailer.Version} Version
 */
function(Brickrouge, Adjust, Version) {

	const AdjustThumbnailVersion = class extends Adjust {

		/**
		 * @param {Element} el
		 * @param {object} options
		 */
		constructor(el, options)
		{
			super()

			this.element = el
			this.options = options

			const width = el.querySelector('input[name="width"]') || el.querySelector('input[name$="[width]"]')
			const height = el.querySelector('input[name="height"]') || el.querySelector('input[name$="[height]"]')
			const method = el.querySelector('select[name="method"]') || el.querySelector('select[name$="[method]"]')
			const format = el.querySelector('select[name="format"]') || el.querySelector('select[name$="[format]"]')
			const quality = el.querySelector('input[name="quality"]') || el.querySelector('input[name$="[quality]"]')

			this.elements = {
				width: width,
				height: height,
				method: method,
				format: format,
				quality: quality,
				'no-upscale': el.querySelector('input[name="no-upscale"]') || el.querySelector('input[name$="[no-upscale]"]'),
				background: el.querySelector('input[name="background"]') || el.querySelector('input[name$="[background]"]'),
				filter: el.querySelector('input[name="filter"]') || el.querySelector('input[name$="[filter]"]')
			}

			Object.each(this.elements, (control) => {

				if (!control) return

				control.addEventListener('change', this.notifyChange.bind(this))

			})

			function checkMethod()
			{
				switch (method.value)
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
				const value = format.value

				// quality.getParent().setStyle('display', (value != 'jpeg') ? 'none' : '')
			}

			checkMethod()
			checkQuality()

			method.addEventListener('change', checkMethod)
			format.addEventListener('change', checkQuality)
		}

		/**
		 * Notifies change.
		 */
		notifyChange()
		{
			this.notify(new Adjust.ChangeEvent(this, this.value))
		}

		/**
		 * @returns {string} Serialized version.
		 */
		get value()
		{
			const options = this.element.toQueryString().parseQueryString()

			return Version.serialize(options)
		}

		/**
		 * @param {string} value Serialized version.
		 */
		set value(value)
		{
			const options = Version.normalize(Version.unserialize(value))

			Object.each(options, (value, key) => {

				if (!(key in this.elements))
				{
					return
				}

				if (key == 'no-upscale' || key == 'no-interlace')
				{
					this.elements[key].checked = value
				}
				else
				{
					this.elements[key].value = value
				}

			})
		}
	}

	Brickrouge.register('AdjustThumbnailVersion', (element, options) => {

		return new AdjustThumbnailVersion(element, options)

	})

})
