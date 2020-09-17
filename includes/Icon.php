<?php
namespace Cosmos;
use \Html;
class Icon {
	private static $icons = [];

	private static $iconSources = [
		// TODO: Make this 28x28
		'avatar' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 14 2 A 6 6 180 0 1 14 14 A 6 6 180 0 1 14 2 Z M 2 26 L 2 23 Q 2 17 8 17 L 20 17 Q 26 17 26 23 L 26 26 Z'
					]
				]
			]
		],
		'edit' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 20 2 L 26 8 L 8 26 L 2 26 L 2 20 Z M 16 6 L 22 12'
					]
				]
			]
		],
		'talk' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 2 2 L 26 2 L 26 20 L 14 20 L 8 26 L 8 20 L 2 20 Z'
					]
				]
			]
		],
		'view' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 2 14 Q 14 0 26 14 Q 14 28 2 14 Z M 14 10 Q 18 10 18 14 Q 18 18 14 18 Q 10 18 10 14 Q 10 10 14 10 Z'
					]
				]
			]
		],
		'sidebar' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 2 2 L 15 2 L 15 26 L 2 26 Z'
					]
				],
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 19 2 L 26 2 L 26 26 L 19 26 Z'
					]
				]
			]
		],
		'back' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 10 2 L 10 6 Q 26 6 26 16 Q 26 26 14 26 Q 22 26 22 20 Q 22 14 10 14 L 10 18 L 2 10 Z'
					]
				]
			]
		],
		'cancel' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 6 2 L 14 10 L 22 2 L 26 6 L 18 14 L 26 22 L 22 26 L 14 18 L 6 26 L 2 22 L 10 14 L 2 6 Z'
					]
				]
			]
		],
		'search' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 12 2 A 10 10 0 0 0 12 22 A 10 10 0 0 0 12 2 Z M 19 19 L 26 26'
					]
				]
			]
		],
		'notification' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 2 21 L 6 15 L 6 10 A 5 5 180 0 1 22 10 L 22 15 L 26 21 Z M 18 21.5 A 4 4.5 180 0 1 10 21.5'
					]
				]
			]
		],
		'message' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 2 5 L 26 5 L 26 23 L 2 23 Z M 2 9 L 14 19 L 26 9 L 26 23 L 2 23 Z M 2 23 L 10 15 Z M 26 23 L 18 15 Z'
					]
				]
			]
		],
		'activity' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 2 14 L 6 14 L 10 6 L 18 22 L 22 14 L 26 14'
					]
				]
			]
		],
		'sun' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 9 14 A 5 5 180 0 1 19 14 A 5 5 180 0 1 9 14 Z M 14 6 L 14 2 Z M 14 22 L 14 26 Z M 2 14 L 6 14 Z M 22 14 L 26 14 Z M 5 5 L 8 8 Z M 20 8 L 23 5 Z M 20 20 L 23 23 Z M 5 23 L 8 20 Z'
					]
				]
			]
		],
		'moon' => [
			'width' => 28,
			'height' => 28,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 16 2 A 12 12 360 1 1 2 16 M 16 2 A 6 6 240 1 1 2 16'
					]
				]
			]
		],
		'dropdown' => [
			'width' => 14,
			'height' => 14,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 2 5 L 12 5 L 7 10 Z'
					]
				]
			]
		],
		'level-2-dropdown' => [
			'width' => 12,
			'height' => 12,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M11.707 3.293a.999.999 0 0 0-1.414 0L6 7.586 1.707 3.293A.999.999 0 1 0 .293 4.707l5 5a.997.997 0 0 0 1.414 0l5-5a.999.999 0 0 0 0-1.414'
					]
				]
			]
		],
		'bullet' => [
			'width' => 14,
			'height' => 14,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M 7 4 A 3 3 180 0 0 7 10 A 3 3 180 0 0 7 4 Z'
					]
				]
			]
		],
		'close' => [
			'width' => 14,
			'height' => 14,
			'content' => [
				[
					'type' => 'line',
					'attributes' => [
						'x1' => 2, 'y1' => 2, 'x2' => 12, 'y2' => 12
					]
				],
				[
					'type' => 'line',
					'attributes' => [
						'x1' => 2, 'y1' => 12, 'x2' => 12, 'y2' => 2
					]
				],
			]
		],
		'recentchanges' => [
			'width' => 18,
			'height' => 18,
			'content' => [
				[
					'type' => 'polyline',
					'attributes' => [
						'points' => '22 12 18 12 15 21 9 3 6 12 2 12'
					]
				],
			]
		],
		'admindashboard' => [
			'width' => 18,
			'height' => 18,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M50.2,32.8h-4.3v-4.3h4.3V32.8z M67.5,45.8h-4.3v4.3h4.3V45.8z M37.2,32.8h-4.3v4.3h4.3V32.8z
		 M32.8,45.8h-4.3v4.3h4.3V45.8z M80.5,22l-2.2-2.2L50.2,41.5h-4.3c-2.4,0-4.3,1.9-4.3,4.3v4.3c0,2.4,1.9,4.3,4.3,4.3h4.3
		c2.4,0,4.3-1.9,4.3-4.3v-4L80.5,22z M73.6,39.7c0.8,2.6,1.3,5.4,1.3,8.3c0,14.8-12,26.9-26.9,26.9S21.1,62.8,21.1,48
		s12-26.9,26.9-26.9c5.2,0,10,1.5,14.2,4.1l4.1-4.1c-5.2-3.5-11.4-5.6-18.2-5.6c-18,0-32.5,14.6-32.5,32.5S30.1,80.5,48,80.5
		S80.5,65.9,80.5,48c0-4.5-0.9-8.8-2.6-12.6L73.6,39.7z'
					]
				],
			]
		],
		 'explore' => [
			'width' => 91,
			'height' => 91,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M44.996,73.604c9.092,0,17.404-4.302,22.654-11.602l4.641,4.642l2.404-2.404L62.742,52.286   c1.092-1.459,1.828-3.132,2.154-4.919h4.564c-0.141,2.075-0.533,4.111-1.188,6.072l3.225,1.076   c0.949-2.842,1.432-5.819,1.432-8.849c0-15.404-12.531-27.936-27.934-27.936c-15.404,0-27.938,12.531-27.938,27.936   S29.592,73.604,44.996,73.604z M61.635,36.868c-1.5-1.5-3.336-2.533-5.332-3.059c2.975-0.641,5.803-1.541,8.449-2.669   c2.684,3.642,4.383,8.048,4.711,12.826h-4.438C64.744,41.286,63.566,38.801,61.635,36.868z M53.279,53.645   c-2.25,0-4.363-0.876-5.955-2.468c-1.59-1.59-2.465-3.703-2.465-5.951c0-2.247,0.877-4.361,2.467-5.952s3.703-2.466,5.953-2.466   c2.248,0,4.361,0.875,5.951,2.465s2.465,3.705,2.467,5.954c0,2.249-0.877,4.362-2.467,5.952S55.527,53.645,53.279,53.645z    M44.996,70.204c-0.48,0-0.955-0.018-1.428-0.045c-1.939-2.844-3.496-6.23-4.619-9.969c1.865-0.197,3.766-0.308,5.693-0.308   c1.854,0,3.682,0.092,5.471,0.273c-1.133,3.786-2.703,7.182-4.654,10.033C45.305,70.192,45.15,70.204,44.996,70.204z    M44.643,56.482c-2.221,0-4.408,0.141-6.555,0.389c-0.668-3.026-1.07-6.223-1.174-9.504h4.744c0.428,2.34,1.543,4.495,3.262,6.214   c1.684,1.684,3.785,2.795,6.07,3.241c-0.002,0.006-0.004,0.012-0.004,0.019C48.91,56.609,46.793,56.482,44.643,56.482z    M44.922,36.87c-1.932,1.933-3.109,4.417-3.393,7.097h-4.615c0.102-3.212,0.494-6.352,1.145-9.335   c2.154,0.25,4.352,0.393,6.584,0.393c0.941,0,1.875-0.026,2.805-0.07C46.535,35.473,45.684,36.108,44.922,36.87z M33.514,43.967   h-12.99c0.322-4.68,1.961-9.001,4.551-12.598c2.994,1.227,6.225,2.153,9.617,2.765C34.018,37.288,33.615,40.596,33.514,43.967z    M33.516,47.367c0.102,3.439,0.512,6.803,1.203,10c-3.365,0.605-6.574,1.521-9.551,2.73c-2.645-3.623-4.318-7.993-4.645-12.73   H33.516z M27.479,62.827c2.531-0.947,5.244-1.66,8.066-2.154c0.922,3.195,2.146,6.167,3.629,8.827   C34.658,68.397,30.631,66.044,27.479,62.827z M49.738,69.751c1.553-2.728,2.826-5.794,3.783-9.12   c3.102,0.528,6.063,1.33,8.807,2.404C58.893,66.483,54.533,68.821,49.738,69.751z M64.662,60.323   c-3.191-1.351-6.656-2.354-10.309-2.995c0.023-0.111,0.043-0.228,0.066-0.34c2.16-0.205,4.189-1.004,5.918-2.299l4.873,4.875   C65.033,59.823,64.848,60.073,64.662,60.323z M62.434,28.431c-2.762,1.09-5.742,1.9-8.865,2.436   c-0.961-3.381-2.254-6.502-3.826-9.27C54.654,22.563,59.045,25.001,62.434,28.431z M45.473,21.144   c1.971,2.885,3.553,6.354,4.689,10.2c-1.807,0.185-3.65,0.28-5.52,0.28c-1.943,0-3.857-0.111-5.734-0.313   c1.125-3.803,2.693-7.257,4.654-10.135c0.475-0.027,0.951-0.045,1.434-0.045C45.154,21.132,45.313,21.141,45.473,21.144z    M39.166,21.837c-1.502,2.699-2.729,5.738-3.654,8.991c-2.857-0.502-5.598-1.228-8.152-2.194   C30.527,25.354,34.598,22.955,39.166,21.837z'
					]
				],
			]
		]
	];

	public static function getIcon( string $iconName ) : ?Icon {
		if ( isset( Icon::$icons[$iconName] ) ) {
			// If the requested icon is already part of the icon array, just return
			// it immediately
			return Icon::$icons[$iconName];
		} elseif ( isset( Icon::$iconSources[$iconName] ) ) {
			// Otherwise, if the requested icon is part of the iconSources array,
			// construct a new Icon object using the iconSources info, add it to the
			// icon array and return it
			$source = Icon::$iconSources[$iconName];
			Icon::$icons[$iconName] = new Icon( $source['width'], $source['height'],
					$source['content'] );
			return Icon::$icons[$iconName];
		} else {
			// Finally, if the requested icon is not part of either array, just
			// return null - no such icon exists
			return null;
		}
	}

	private $defaultWidth;
	private $defaultHeight;
	private $content;

	public function __construct( int $defaultWidth, int $defaultHeight,
			array $content ) {
		$this->defaultWidth = $defaultWidth;
		$this->defaultHeight = $defaultHeight;
		$this->content = $content;
	}

	public function makeSvg( int $width = -1, int $height = -1,
			array $attributes = [] ) : string {
		if ( $width < 0 ) {
			$width = $this->defaultWidth;
		}

		if ( $height < 0 ) {
			$height = $this->defaultWidth;
		}

		$attributes['width'] = $width;
		$attributes['height'] = $height;
		$attributes['viewBox'] = "0 0 $width $height";

		$result = Html::openElement( 'svg', $attributes );
		$result .= $this->makeInnerSvg( $width, $height );
		$result .= Html::closeElement( 'svg' );

		return $result;
	}

	public function makeInnerSvg( int $width = -1, int $height = -1 ) : string {
		if ( $width < 0 ) {
			$width = $this->defaultWidth;
		}

		if ( $height < 0 ) {
			$height = $this->defaultHeight;
		}

		$result = '';

		foreach ( $this->content as $element ) {
			$this->makeElement( $result, $element, $width, $height );
		}

		return $result;
	}

	protected function makeElement( string &$result, array $element,
			int $width, int $height ) : void {

		// TODO: Implement rescaling of element to match the given width and height

		$result .= Html::element( $element['type'], $element['attributes'] );
	}

}
