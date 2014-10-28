require('./bootstrap.js')

var chai = require('chai')
, expect = chai.expect
, thumbnailer = require('../lib/Module.js')
, Thumbnail = thumbnailer.Thumbnail

describe('Thumbnail', function() {

	describe('#toString', function() {

		it("'src' should be in the query string", function() {

			var t = new Thumbnail('/public/image.png', '100x200?q=60')

			expect(t.toString()).to.equal('/api/thumbnail/100x200?q=60&s=%2Fpublic%2Fimage.png')

		})

		it("should use image API", function() {

			var t = new Thumbnail('/api/images/123', '100x200?q=60')

			expect(t.toString()).to.equal('/api/images/123/100x200?q=60')

		})

	})

})
