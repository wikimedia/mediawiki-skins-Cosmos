<?php

namespace MediaWiki\Skin\Cosmos;

use ExtensionRegistry;
use Html;
use MediaWiki\MediaWikiServices;
use MessageLocalizer;
use ObjectCache;
use Sanitizer;
use Title;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;

class CosmosNavigation implements ExpirationAwareness {
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
	 * @param array $nodes
	 * @param array $children
	 * @param bool $bolded
	 * @return string
	 */
	public function getSubMenu( $nodes, $children, $bolded = false ) {
		$menu = '';
		$val = 0;
		foreach ( $children as $key => $val ) {
			$link_html = htmlspecialchars( $nodes[$val]['text'] );
			if ( !empty( $nodes[$val]['children'] ) ) {
				$link_html .= Icon::getIcon( 'level-2-dropdown' )
					->makeSvg( 12, 12, [
						'id' => 'wds-icons-menu-control-tiny',
						'class' => 'wds-icon wds-icon-tiny wds-dropdown-chevron'
					] );
			}

			$menu_item = Html::rawElement( 'a', [
				'href' => !empty( $nodes[$val]['href'] ) ? $nodes[$val]['href'] : '#',
				'class' => ( !empty( $nodes[$val]['children'] ) ? 'wds-dropdown-level-2__toggle' : null ),
				'rel' => $nodes[$val]['internal'] ? null : 'nofollow'
			], $link_html ) . "\n";

			if ( !empty( $nodes[$val]['children'] ) ) {
				$menu_item .= '<div class="wds-is-not-scrollable wds-dropdown-level-2__content" id="p-' .
					Sanitizer::escapeIdForAttribute( $nodes[$val]['text'] ) . '" aria-labelledby="p-' .
					Sanitizer::escapeIdForAttribute( $nodes[$val]['text'] ) . '-label">';
				$menu_item .= $this->getSubMenu( $nodes, $nodes[$val]['children'] );
				$menu_item .= '</div>';
			}

			if ( !empty( $nodes[$val]['children'] ) ) {
				$stickClass = $key > count( $nodes[$val]['children'] ) - 1 ? 'wds-is-sticked-to-parent ' : '';
				$liClass = $stickClass . 'wds-dropdown-level-2';
			} else {
				$liClass = false;
			}
			$menu .= Html::rawElement( 'li',  [
				'id' => Sanitizer::escapeIdForAttribute( 'n-' . strtr( $nodes[$val]['text'], ' ', '-' ) ),
				'class' => $liClass
			], $menu_item );
		}

		$menu = Html::rawElement(
			'div',
			[],
			'<ul class="wds-list wds-is-linked' . ( $bolded === true ? ' wds-has-bolded-items' : '' ) .
				'">' . $menu . '</ul>'
		);

		return $menu;
	}

	/**
	 * @param array $lines
	 * @return string
	 */
	public function getMenu( $lines ) {
		$menu = '';
		$nodes = $this->parse( $lines );

		if ( count( $nodes ) > 0 ) {
			MediaWikiServices::getInstance()->getHookContainer()->run( 'getCosmosNavigation', [ &$nodes ] );

			$mainMenu = [];

			foreach ( $nodes[0]['children'] as $key => $val ) {
				if ( isset( $nodes[$val]['children'] ) ) {
					$mainMenu[$val] = $nodes[$val]['children'];
				}

				$menu .= '<li class="wds-tabs__tab">';
				if ( !empty( $nodes[$val]['children'] ) ) {
					$menu .= '<div class="wds-dropdown" id="p-' .
						Sanitizer::escapeIdForAttribute( $nodes[$val]['text'] ) .
						'" aria-labelledby="p-' . Sanitizer::escapeIdForAttribute( $nodes[$val]['text'] ) . '-label">';
				}

				$menu .= '<div class="wds-tabs__tab-label ';
				if ( !empty( $nodes[$val]['children'] ) ) {
					$menu .= ' wds-dropdown__toggle';
				}

				$menu .= '" id="p-' . Sanitizer::escapeIdForAttribute( $nodes[$val]['text'] ) . '-label">';
				if (
					!empty( $nodes[$val]['href'] ) &&
					$nodes[$val]['text'] !== 'Navigation' &&
					$nodes[$val]['text'] !== $this->messageLocalizer->msg( 'cosmos-explore' )->text()
				) {
					$exploreHREF = htmlspecialchars( $nodes[$val]['href'] );
				} else {
					$exploreHREF = '#';
				}
				$menu .= '<a href="' . $exploreHREF . '"';
				if ( !isset( $nodes[$val]['internal'] ) || !$nodes[$val]['internal'] ) {
					$menu .= ' rel="nofollow"';
				}

				if ( $nodes[$val]['text'] === $this->messageLocalizer->msg( 'cosmos-explore' )->text() ) {
					$exploreIcon = Icon::getIcon( 'explore' )->makeSvg(
						91,
						91,
						[ 'id' => 'cosmos-icons-explore', 'class' => 'wds-icon' ]
					);
					$exploreStyle = 'style="padding-top: 2px;"';
				} else {
					$exploreIcon = $exploreStyle = '';
				}

				$menu .= '>' . $exploreIcon . '<span ' . $exploreStyle . '>' .
					htmlspecialchars( $nodes[$val]['text'] ) . '</span>';
				if ( !empty( $nodes[$val]['children'] ) ) {
					$menu .= Icon::getIcon( 'dropdown' )->makeSvg(
						14,
						14,
						[
							'id' => 'wds-icons-dropdown-tiny',
							'class' => 'wds-icon wds-icon-tiny wds-dropdown__toggle-chevron'
						]
					);
				}

				$menu .= '</a></div>';
				if ( !empty( $nodes[$val]['children'] ) ) {
					$menu .= '<div class="wds-is-not-scrollable wds-dropdown__content">';
					$menu .= $this->getSubMenu( $nodes, $nodes[$val]['children'], true );
					$menu .= '</div></div>';
				}

			}
			$menu .= '</li>';
			$menu = Html::rawElement( 'li', [
				'class' => 'wds-tabs__tab'
			], $menu );
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
					// @phan-suppress-next-line PhanTypeInvalidDimOffset
					$node['parentIndex'] = $nodes[$i]['parentIndex'];
				} elseif ( $node['depth'] == $lastDepth + 1 ) {
					$node['parentIndex'] = $i;
				} else {
					for ( $x = $i;$x >= 0;$x-- ) {
						if ( $x == 0 ) {
							$node['parentIndex'] = 0;
							break;
						}

						// @phan-suppress-next-line PhanTypeInvalidDimOffset
						if ( $nodes[$x]['depth'] == $node['depth'] - 1 ) {
							$node['parentIndex'] = $x;
							break;
						}
					}
				}

				if (
					!empty( $node['original'] ) && (
						$node['original'] == 'SEARCH' ||
						$node['original'] == 'TOOLBOX' ||
						$node['original'] == 'LANGUAGES'
					)
				) {
					continue;
				}

				$nodes[$i + 1] = $node;
				$nodes[ $node['parentIndex'] ?? 0 ]['children'][] = $i + 1;
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
					$href = $title->fixSpecialName()
						->getLocalURL();
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
		return $this->getMessageAsArray( 'Cosmos-navigation' );
	}

	/**
	 * @param string $messageKey
	 * @return array|null
	 */
	private function getMessageAsArray( $messageKey ) {
		$cosmosNavigationMessage = $this->messageLocalizer->msg( $messageKey )->inContentLanguage()->text();

		$exploreChildURL = null;
		$exploreChildText = null;

		$forceExploreChildURL = null;
		$forceExploreChildText = null;

		if (
			ExtensionRegistry::getInstance()->isLoaded( 'Video' ) &&
			(
				strpos( $cosmosNavigationMessage, '{$NEWVIDEOS_CONDITIONAL}' ) !== false ||
				strpos( $cosmosNavigationMessage, '{$NEWVIDEOS}' ) !== false
			)
		) {
			$exploreChildURL = "**" . htmlspecialchars( Title::newFromText( 'NewVideos', NS_SPECIAL ) ) . '|';
			$exploreChildText = "newvideos";

			if ( strpos( $cosmosNavigationMessage, '{$WANTEDPAGES_FORCE}' ) !== false ) {
				$forceExploreChildURL = "\n**" .
					htmlspecialchars( Title::newFromText( 'WantedPages', NS_SPECIAL ) ) . '|';
				$forceExploreChildText = 'wantedpages';
			}
		} elseif (
			strpos( $cosmosNavigationMessage, '{$WANTEDPAGES_CONDITIONAL}' ) !== false ||
			strpos( $cosmosNavigationMessage, '{$WANTEDPAGES}' ) !== false
		) {
			$exploreChildURL = "**" . htmlspecialchars( Title::newFromText( 'WantedPages', NS_SPECIAL ) ) . '|';
			$exploreChildText = 'wantedpages';
		}

		$cleanedMsg = preg_replace(
			'/(\{\$NEWVIDEOS\})|(\{\$WANTEDPAGES\})|(\{\$NEWVIDEOS_CONDITIONAL\})' .
				'|(\{\$WANTEDPAGES_CONDITIONAL\})|(\{\$WANTEDPAGES_FORCE\})/',
			'',
			$cosmosNavigationMessage
		);
		$message = trim(
			$cleanedMsg . $exploreChildURL . $exploreChildText . $forceExploreChildURL . $forceExploreChildText
		);

		if ( $this->messageLocalizer->msg( $messageKey, $message )->exists() ) {
			$lines = explode( "\n", $message );
			if ( count( $lines ) > 0 ) {
				return $lines;
			}
		}

		return null;
	}
}
