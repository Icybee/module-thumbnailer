Brickrouge.Widget.PopThumbnailVersion = new Class
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

				this.attachAdjust(widget)

				this.popover.show()
				this.popover.addEvent('action', this.onAction.bind(this))

			}.bind(this)).get({ value: this.getValue() })
		}
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

				this.setValue(ev.popover.adjust.getValue())

				break

			case 'remove':

				this.setValue(null)

				break

			case 'cancel':

				this.setValue(this.resetValue)

				break
		}

		this.popover.hide()
	}
});
