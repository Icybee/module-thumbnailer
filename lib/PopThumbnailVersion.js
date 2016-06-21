define('icybee/thumbnailer/pop-thumbnail-version', [

	'icybee/spinner',
	'icybee/adjust-popover'

],
/**
 *
 * @param {Icybee.Spinner} Spinner
 * @param {Icybee.AdjustPopover} AdjustPopover
 * @returns {{}}
 */
function (Spinner, AdjustPopover) {

	const WIDGET_URL = 'adjust-thumbnail-version/popup'

    return class extends Spinner {

	    createPopover(callback) {

		    new Request.Widget(WIDGET_URL, popoverElement => {

			    callback(new AdjustPopover(popoverElement, { anchor: this.element }))

		    }).get({ value: this.value })

	    }
	}

})

/**
 * @param {Brickrouge} Brickrouge
 */
!function (Brickrouge) {

	let Constructor

	Brickrouge.register('PopThumbnailVersion', (element, options) => {

		if (!Constructor)
		{
			Constructor = require('icybee/thumbnailer/pop-thumbnail-version')
		}

		return new Constructor(element, options)

	})

} (Brickrouge)
