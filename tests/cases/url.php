<?php

return [

	/*
	 * version
	 */

	[
		'/repository/files/claire.png', 'icon',

		'/api/thumbnail/icon?s=%2Frepository%2Ffiles%2Fclaire.png'

	],

	/*
	 *
	 */

	[
		'/repository/files/claire.png', [

			'width' => 100

		],

		'/api/thumbnail/100x/fixed-width?s=%2Frepository%2Ffiles%2Fclaire.png'

	],

	[
		'/repository/files/claire.png', [

			'height' => 100

		],

		'/api/thumbnail/x100/fixed-height?s=%2Frepository%2Ffiles%2Fclaire.png'

	],

	[
		'/repository/files/claire.png', [

			'width' => 100,
			'height' => 100

		],

		'/api/thumbnail/100x100?s=%2Frepository%2Ffiles%2Fclaire.png'

	],

	[
		'/repository/files/claire.png', [

			'width' => 100,
			'height' => 100,
			'method' => 'fill'

		],

		'/api/thumbnail/100x100?s=%2Frepository%2Ffiles%2Fclaire.png'

	],

	[
		'/repository/files/claire.png', [

			'width' => 100,
			'height' => 100,
			'method' => 'surface'

		],

		'/api/thumbnail/100x100/surface?s=%2Frepository%2Ffiles%2Fclaire.png'

	],

	[
		'/repository/files/claire.png', [

			'width' => 100,
			'height' => 100,
			'method' => 'surface',
			'filter' => 'grayscale'

		],

		'/api/thumbnail/100x100/surface?ft=grayscale&s=%2Frepository%2Ffiles%2Fclaire.png'

	],

	[
		'/repository/files/claire.png', [

			'width' => 100,
			'height' => 100,
			'method' => 'surface',
			'filter' => 'grayscale',
			'format' => 'png'

		],

		'/api/thumbnail/100x100/surface.png?ft=grayscale&s=%2Frepository%2Ffiles%2Fclaire.png'

	]

];