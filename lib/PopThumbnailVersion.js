define('icybee/thumbnailer/pop-thumbnail-version', [

	'brickrouge',
	'icybee/spinner',
	'icybee/adjust-popover'

],

/**
 * @param {Brickrouge} Brickrouge
 * @param {Icybee.Spinner} Spinner
 * @param {Icybee.AdjustPopover} AdjustPopover
 *
 * @returns {Icybee.Thumbnailer.PopThumbnailVersion}
 */
function (Brickrouge, Spinner, AdjustPopover) {

	const WIDGET_URL = 'adjust-thumbnail-version/popup'

    class PopThumbnailVersion extends Spinner
    {
	    createPopover(callback)
	    {
		    new Request.Widget(WIDGET_URL, popoverElement => {

			    callback(new AdjustPopover(popoverElement, { anchor: this.element }))

		    }).get({ value: this.value })
	    }
	}

	Brickrouge.register('PopThumbnailVersion', (element, options) => {

		return new PopThumbnailVersion(element, options)

	})

	return PopThumbnailVersion

})
