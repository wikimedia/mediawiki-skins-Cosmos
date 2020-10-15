<?php
/**
 * CosmosToolbar class
 *
 * @package MediaWiki
 * @subpackage Skins
 *
 * @author Inez Korczynski <inez@wikia.com>
 * @author Christian Williams
 * @author Universal Omega
 */

namespace MediaWiki\Skin\Cosmos;

use Hooks;
use MessageLocalizer;
use ObjectCache;
use Sanitizer;
use Title;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;

class CosmosToolbar implements ExpirationAwareness {
	/** @var MessageLocalizer */
	private $messageLocalizer;

	/**
	 * @param MessageLocalizer $messageLocalizer
	 */
	public function __construct( MessageLocalizer $messageLocalizer ) {
		$this->messageLocalizer = $messageLocalizer;
	}

	/**
	 * @return string
	 */
	public function getCode() {
		return $this->getMenu( $this->getMenuLines() );
	}

	/**
	 * @param array $lines
	 * @return string
	 */
	public function getMenu( $lines ) {
		$menu = '';
		$nodes = $this->parse( $lines );

		if ( count( $nodes ) > 0 ) {
			Hooks::run( 'getCosmosToolbar', [ &$nodes ] );

			$mainMenu = [];
			foreach ( $nodes[0]['children'] as $key => $val ) {
				$menu .= '<li id="' .
					Sanitizer::escapeIdForAttribute( 't-' . strtolower( strtr( $nodes[$val]['text'], ' ', '-' ) ) ) . '">';
				$menu .= '<a href="' . ( !empty( $nodes[$val]['href'] ) ? htmlspecialchars( $nodes[$val]['href'] ) : '#' ) . '"';
				if (
					!isset( $nodes[$val]['internal'] ) ||
					!$nodes[$val]['internal']
				) {
					$menu .= ' rel="nofollow"';
				}

				$menu .= '><span>' . htmlspecialchars( $nodes[$val]['text'] ) . '</span>';
				$menu .= '</a>';

			}

			$menu .= '</li>';
			$menu = preg_replace( '/<!--b-->(.*)<!--e-->/U', '', $menu );
			$menuHash = hash( 'md5', serialize( $nodes ) );

			foreach ( $nodes as $key => $val ) {
				if ( !isset( $val['depth'] ) || $val['depth'] == 1 ) {
					unset( $nodes[$key] );
				}
				unset( $nodes[$key]['parentIndex'] );
				unset( $nodes[$key]['depth'] );
				unset( $nodes[$key]['original'] );
			}

			$nodes['mainMenu'] = $mainMenu;
			$memc = ObjectCache::getLocalClusterInstance();
			$memc->set( $menuHash, $nodes, self::TTL_DAY * 3 );

			return $menu;
		}
	}

	/**
	 * @param array $lines
	 * @return array
	 */
	public function parse( $lines ) {
		$nodes = [];
		$lastDepth = 0;
		$i = 0;
		if ( is_array( $lines ) && count( $lines ) > 0 ) {
			foreach ( $lines as $line ) {
				if ( trim( $line ) === '' ) {
					// ignore empty lines
					continue;

				}

				$node = $this->parseLine( $line );
				$node['depth'] = strrpos( $line, '*' ) + 1;

				if ( $node['depth'] == $lastDepth ) {
					$node['parentIndex'] = $nodes[$i]['parentIndex'];
				} elseif ( $node['depth'] == $lastDepth + 1 ) {
					$node['parentIndex'] = $i;
				} else {
					for ( $x = $i;$x >= 0;$x-- ) {
						if ( $x == 0 ) {
							$node['parentIndex'] = 0;
							break;
						}

						if ( $nodes[$x]['depth'] == $node['depth'] - 1 ) {
							$node['parentIndex'] = $x;
							break;
						}
					}
				}

				$nodes[$i + 1] = $node;
				$nodes[$node['parentIndex']]['children'][] = $i + 1;
				$lastDepth = $node['depth'];
				$i++;
			}
		}

		return $nodes;
	}

	/**
	 * @param string $line
	 * @return array
	 */
	public function parseLine( $line ) {
		$lineTmp = explode( '|', trim( $line, '* ' ), 2 );
		// for external links defined as [http://example.com] instead of just http://example.com
		$lineTmp[0] = trim( $lineTmp[0], '[]' );
		$internal = false;

		if ( count( $lineTmp ) == 2 && $lineTmp[1] != '' ) {
			$link = trim( $this->messageLocalizer->msg( $lineTmp[0] )->inContentLanguage()->text() );
			$line = trim( $lineTmp[1] );
		} else {
			$link = trim( $lineTmp[0] );
			$line = trim( $lineTmp[0] );
		}

		if ( $this->messageLocalizer->msg( $line )->exists() ) {
			$text = $this->messageLocalizer->msg( $line )->text();
		} else {
			$text = $line;
		}

		if ( !$this->messageLocalizer->msg( $lineTmp[0] )->exists() ) {
			$link = $lineTmp[0];
		}

		if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $link ) ) {
			$href = $link;
		} else {
			if ( empty( $link ) ) {
				$href = '#';
			} elseif ( $link[0] == '#' ) {
				$href = '#';
			} else {
				$title = Title::newFromText( $link );
				if ( is_object( $title ) ) {
					$href = $title->fixSpecialName()->getLocalURL();
					$internal = true;
				} else {
					$href = '#';
				}
			}
		}

		return [
			'original' => $lineTmp[0],
			'text' => $text,
			'href' => $href,
			'internal' => $internal
		];
	}

	/**
	 * @return array|null
	 */
	private function getMenuLines() {
		return $this->getMessageAsArray( 'Cosmos-toolbar' );
	}

	/**
	 * @param string $messageKey
	 * @return array|null
	 */
	private function getMessageAsArray( $messageKey ) {
		$message = trim( $this->messageLocalizer->msg( $messageKey )->inContentLanguage()->text() );

		if ( $this->messageLocalizer->msg( $messageKey, $message )->exists() ) {
			$lines = explode( "\n", $message );
			if ( count( $lines ) > 0 ) {
				return $lines;
			}
		}

		return null;
	}
}
