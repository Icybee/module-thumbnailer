/*
 * setup
 */

require('mootools')
var chai = require('chai')
, expect = chai.expect

String.implement({

	parseQueryString: function(decodeKeys, decodeValues){
		if (decodeKeys == null) decodeKeys = true;
		if (decodeValues == null) decodeValues = true;

		var vars = this.split(/[&;]/),
			object = {};
		if (!vars.length) return object;

		vars.each(function(val){
			var index = val.indexOf('=') + 1,
				value = index ? val.substr(index) : '',
				keys = index ? val.substr(0, index - 1).match(/([^\]\[]+|(\B)(?=\]))/g) : [val],
				obj = object;
			if (!keys) return;
			if (decodeValues) value = decodeURIComponent(value);
			keys.each(function(key, i){
				if (decodeKeys) key = decodeURIComponent(key);
				var current = obj[key];

				if (i < keys.length - 1) obj = obj[key] = current || {};
				else if (typeOf(current) == 'array') current.push(value);
				else obj[key] = current != null ? [current, value] : value;
			});
		});

		return object;
	},

	cleanQueryString: function(method){
		return this.split('&').filter(function(val){
			var index = val.indexOf('='),
				key = index < 0 ? '' : val.substr(0, index),
				value = val.substr(index + 1);

			return method ? method.call(null, key, value) : (value || value === 0);
		}).join('&');
	}

});

var thumbnailer = require('../lib/Module.js')
, Version = thumbnailer.Version

/*
 * specs
 */

describe("Version", function() {

	describe('#widen', function() {

		it("should widen options", function() {

			var options = Version.widen(Version.defaults)

			expect(Object.keys(options).join(' ')).to.equal('background default filter format height method no-interlace no-upscale overlay path quality src width')

		})

	})

	describe('#shorten', function() {

		it("should shorten options", function() {

			expect(Object.keys(Version.shorten(Version.defaults)).join(' ')).to.equal('b d ft f h m ni nu o p q s w')

		})

	})

	describe("#normalize", function() {

		[

			[ "should replace empty method with 'fill'",

				Version.defaults, {

					method: null

				}
			],

			[ "should sort options by key",

				Object.merge({}, Version.defaults, {

					height: 200,
					width: 100

				}), {

				width: 100,
				height: 200

			}],

			[ "should add implicit method 'fixed-width'",

				Object.merge({}, Version.defaults, {

					method: "fixed-width",
					width: 100

				}), {

				width: 100

			}],

			[ "should add implicit method 'fixed-height'",

				Object.merge({}, Version.defaults, {

					method: "fixed-height",
					height: 200

				}), {

				height: 200

			}],

			[ "should fix incorrect method to 'fixed-width",

				Object.merge({}, Version.defaults, {

					method: 'fixed-width',
					width: 100

				}), {

					width: 100,
					method: 'surface'

				}

			],

			[ "should fix incorrect method to 'fixed-height",

				Object.merge({}, Version.defaults, {

					height: 100,
					method: 'fixed-height'

				}), {

					height: 100,
					method: 'surface'

				}

			],

			[ "should preserve method when width and height are not defined",

				Object.merge({}, Version.defaults, {

					method: 'surface',

				}), {

					method: 'surface'

				}

			]

		].forEach(function(testCase) {

			var message = testCase[0]
			, expected = testCase[1]
			, options = testCase[2]

			it(message, function() {

				expect(expected).to.deep.equal(Version.normalize(options))

			})

		})

	})

	describe('#filter', function() {

		[
			[ "should remove implicit method 'fill' when both 'width' and 'height' are defined", {

				width: 200,
				height: 100,
				method: 'fill'

				}, {

				width: 200,
				height: 100

			} ],

			[ "should remove implicit method 'fixed-width' when only 'width' is defined", {

				width: 200,
				method: 'fixed-width'

				}, {

				width: 200

			} ],

			[ "should remove implicit method 'fixed-height' when only 'height' is defined", {

				height: 100,
				method: 'fixed-height'

				}, {

				height: 100

			} ]

		].forEach(function(testCase) {

			it(testCase[0], function() {

				expect(Version.filter(testCase[1])).to.deep.equal(testCase[2])

			})

		})

	})

	!function() {

		var testCases = [

			[ "it should be empty if all options match defaults",

				Version.defaults, ""

			],

			[ "it should be empty", {

			}, "" ],

			[ "extraneous options should be filtered out", {

				extraneous: '123'

			}, "" ],

			[ "should include width and height", {

				width: 100,
				height: 200

			}, "100x200" ],

			[ "should only include width and height", {

				width: 100,
				height: 200,
				method: 'fill'

			}, "100x200" ],

			[ "should include width, height and format", {

				width: 100,
				height: 200,
				format: 'png'

			}, "100x200.png" ],

			[ "should include width, height and method", {

				width: 100,
				height: 200,
				method: 'surface'

			}, "100x200/surface" ],

			[ "should include width", {

				width: 100

			}, "100x" ],

			[ "should only include width", {

				w: 100,
				m: 'fixed-width'

			}, "100x" ],

			[ "should include height", {

				height: 200

			}, "x200" ],

			[ "should only include height", {

				h: 200,
				m: 'fixed-height'

			}, "x200" ],

			[ "should include additional options", {

				w: 100,
				h: 200,
				f: 'png',
				q: '30',
				filter: 'grayscale'
			}, "100x200.png?ft=grayscale&q=30" ]

		]

		describe('#serialize', function() {

			testCases.forEach(function(testCase) {

				it(testCase[0], function() {

					expect(Version.serialize(testCase[1])).to.equal(testCase[2])

				})

			})

		})

		describe('#toString', function() {

			testCases.forEach(function(testCase) {

				it(testCase[0], function() {

					expect(new Version(testCase[1]).toString()).to.equal(testCase[2])

				})

			})

		})

		describe('#unserialize', function() {

			testCases.forEach(function(testCase) {

				it("should equal", function() {

					expect(Version.filter(testCase[1])).to.deep.equal(Version.unserialize(testCase[2]))

				})

			})

		})

	} ()

})