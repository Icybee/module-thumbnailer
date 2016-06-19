define('icybee/thumbnailer/thumbnail-version', [

	'icybee/pop-adjust',
	'icybee/adjust-popover'

], function (PopAdjust, AdjustPopover) {

	const WIDGET_URL = 'adjust-thumbnail-version/popup'

    return class extends PopAdjust {

	    createPopover(callback) {

		    new Request.Widget(WIDGET_URL, adjust => {

			    callback(new AdjustPopover(adjust, { anchor: this.element }))

		    }).get({ value: this.value })

	    }
	}

})

!function (Brickrouge) {

	let Constructor

	Brickrouge.register('PopThumbnailVersion', (element, options) => {

		if (!Constructor)
		{
			Constructor = require('icybee/thumbnailer/thumbnail-version')
		}

		return new Constructor(element, options)

	})

} (Brickrouge)
