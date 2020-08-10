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
		 'explore' => [
			'width' => 11,
			'height' => 11,
			'content' => [
				[
					'type' => 'path',
					'attributes' => [
						'd' => 'M3.5 3C2.673 3 2 3.673 2 4.5v2.338c.91-.434 2.09-.434 3 0V4.5c0-.397-.159-.785-.437-1.063A1.513 1.513 0 0 0 3.5 3m5 3.5c.537 0 1.045.121 1.5.338V4.5c0-.397-.159-.785-.437-1.063A1.502 1.502 0 0 0 7 4.5v2.338A3.473 3.473 0 0 1 8.5 6.5M11 11a1 1 0 0 1-1-1c0-.827-.673-1.5-1.5-1.5S7 9.173 7 10a1 1 0 1 1-2 0c0-.827-.673-1.5-1.5-1.5S2 9.173 2 10a1 1 0 1 1-2 0V4.5a3.504 3.504 0 0 1 5.977-2.477l.026.027A3.489 3.489 0 0 1 8.5 1c.937 0 1.817.363 2.477 1.023A3.524 3.524 0 0 1 12 4.5V10a1 1 0 0 1-1 1'
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