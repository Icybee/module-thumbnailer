require('./../bootstrap.js')

var chai = require('chai')
, expect = chai.expect
, thumbnailer = require('../../lib/Module.js')
, Thumbnail = thumbnailer.Thumbnail

describe('Thumbnail', function() {

	describe('#toString', function() {

		it("'src' should be in the query string", function() {

			var t = new Thumbnail('/public/image.png', '100x200?q=60')

			expect(t.toString()).to.equal('/api/thumbnail/100x200?q=60&s=%2Fpublic%2Fimage.png')

		})

		it("should use image API with Id", function() {

			var t = new Thumbnail('/images/123', '100x200?q=60')

			expect(t.toString()).to.equal('/images/123/100x200?q=60')

		})

		it("should use image API with UUID", function() {

			var t = new Thumbnail('/images/dc1125fa-aa48-4c1c-93d9-bd0ce001f984', '100x200?q=60')

			expect(t.toString()).to.equal('/images/dc1125fa-aa48-4c1c-93d9-bd0ce001f984/100x200?q=60')

		})

	})

})
