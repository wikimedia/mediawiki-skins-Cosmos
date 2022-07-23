<?php

namespace MediaWiki\Skins\Cosmos;

use ExtensionRegistry;
use Html;
use MediaWiki\MediaWikiServices;
use MessageLocalizer;
use ObjectCache;
use RequestContext;
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
		$memc = ObjectCache::getLocalClusterInstance();

		$contLang = MediaWikiServices::getInstance()->getContentLanguage();
		$lang = RequestContext::getMain()->getLanguage();

		$cache = $lang->getCode() == $contLang->getCode();

		$menuGetter = function (): string {
			return $this->getMenu( $this->getMenuLines() );
		};
		if ( $cache ) {
			$key = $memc->makeKey( 'mCosmosNavigation', 'cosmosNavigation' );
			$menu = $memc->getWithSetCallback( $key, self::TTL_HOUR * 8, $menuGetter );
		} else {
			$menu = $menuGetter();
		}

		return $menu;
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
			$linkHtml = htmlspecialchars( $nodes[$val]['text'] );

			$nodeChildren = $nodes[$val]['children'] ?? [];

			if ( !empty( $nodeChildren ) ) {
				$linkHtml .= Icon::getIcon( 'level-2-dropdown' )->makeSvg( 12, 12, [
					'id' => 'wds-icons-menu-control-tiny',
					'class' => [ 'wds-icon', 'wds-icon-tiny', 'wds-dropdown-chevron' ]
				] );
			}

			$menuItem = Html::rawElement( 'a', [
				'href' => !empty( $nodes[$val]['href'] ) ? $nodes[$val]['href'] : '#',
				'class' => [
					'wds-dropdown-level-2__toggle' => !empty( $nodeChildren )
				],
				'rel' => [ 'nofollow' => empty( $nodes[$val]['internal'] ?? [] ) ]
			], $linkHtml );

			$menuItem .= "\n";

			$text = Sanitizer::escapeIdForAttribute( $nodes[$val]['text'] );

			if ( !empty( $nodeChildren ) ) {
				$menuItem .= Html::rawElement( 'div', [
					'class' => [
						'wds-is-not-scrollable',
						'wds-dropdown-level-2__content'
					],
					'id' => "p-{$text}",
					'aria-labelledby' => "p-{$text}-label"
				], $this->getSubMenu( $nodes, $nodeChildren ) );
			}

			$menu .= Html::rawElement( 'li',  [
				'id' => "n-{$text}",
				'class' => [
					'wds-is-sticked-to-parent' => $key > count( $nodeChildren ) - 1,
					'wds-dropdown-level-2' => !empty( $nodeChildren )
				]
			], $menuItem );
		}

		$menu = Html::rawElement( 'div', [],
			Html::rawElement( 'ul', [
				'class' => [ 'wds-list', 'wds-is-linked', 'wds-has-bolded-items' => $bolded ]
			], $menu )
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
			$mainMenu = [];

			foreach ( $nodes[0]['children'] as $key => $val ) {
				if ( isset( $nodes[$val]['children'] ) ) {
					$mainMenu[$val] = $nodes[$val]['children'];
				}

				$menu .= Html::openElement( 'li', [
					'class' => 'wds-tabs__tab'
				] );

				$text = Sanitizer::escapeIdForAttribute( $nodes[$val]['text'] );

				if ( !empty( $nodes[$val]['children'] ) ) {
					$menu .= Html::openElement( 'div', [
						'class' => 'wds-dropdown',
						'id' => "p-{$text}",
						'aria-labelledby' => "p-{$text}-label"
					] );
				}

				if (
					!empty( $nodes[$val]['href'] ) &&
					$nodes[$val]['text'] !== 'Navigation' &&
					$nodes[$val]['text'] !== $this->messageLocalizer->msg( 'cosmos-explore' )->text()
				) {
					$exploreHref = $nodes[$val]['href'];
				} else {
					$exploreHref = '#';
				}

				if ( $nodes[$val]['text'] === $this->messageLocalizer->msg( 'cosmos-explore' )->text() ) {
					$exploreIcon = Icon::getIcon( 'explore' )->makeSvg(
						91,
						91,
						[ 'id' => 'cosmos-icons-explore', 'class' => 'wds-icon' ]
					);
					$exploreStyle = 'padding-top: 2px;';
				} else {
					$exploreIcon = $exploreStyle = '';
				}

				$menu .= Html::openElement( 'div', [
					'class' => [
						'wds-tabs__tab-label',
						'wds-dropdown__toggle' => !empty( $nodes[$val]['children'] )
					],
					'id' => "p-{$text}-label"
				] );

				$menu .= Html::openElement( 'a', [
					'href' => $exploreHref,
					'rel' => [ 'nofollow' => empty( $nodes[$val]['internal'] ?? [] ) ]
				] );

				$menu .= $exploreIcon;

				$menu .= Html::rawElement( 'span', [
						'style' => $exploreStyle
					], htmlspecialchars( $nodes[$val]['text'] )
				);

				if ( !empty( $nodes[$val]['children'] ) ) {
					$menu .= Icon::getIcon( 'dropdown' )->makeSvg( 14, 14, [
						'id' => 'wds-icons-dropdown-tiny',
						'class' => [
							'wds-icon',
							'wds-icon-tiny',
							'wds-dropdown__toggle-chevron'
						]
					] );
				}

				$menu .= Html::closeElement( 'a' );
				$menu .= Html::closeElement( 'div' );

				if ( !empty( $nodes[$val]['children'] ) ) {
					$menu .= Html::rawElement( 'div', [
						'class' => [
							'wds-is-not-scrollable',
							'wds-dropdown__content'
						]
					], $this->getSubMenu( $nodes, $nodes[$val]['children'], true ) );

					$menu .= Html::closeElement( 'div' );
				}

			}

			$menu .= Html::closeElement( 'li' );

			$menu = Html::rawElement( 'li', [
				'class' => 'wds-tabs__tab'
			], $menu );

			$menu = preg_replace( '/<!--b-->(.*)<!--e-->/U', '', $menu );
			$menuHash = hash( 'md5', serialize( $nodes ) );

			foreach ( $nodes as $key => $val ) {
				if ( !isset( $val['depth'] ) || $val['depth'] == 1 ) {
					unset( $nodes[$key] );
				}

				unset(
					$nodes[$key]['parentIndex'],
					$nodes[$key]['original'],
					$nodes[$key]['depth']
				);
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
				if ( trim( str_replace( '*', '', $line ) ) === '' ) {
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
		return self::extract(
			$this->messageLocalizer->msg( 'Cosmos-navigation' )->inContentLanguage()->text()
		);
	}

	/**
	 * @param string $navigation
	 * @return array|null
	 */
	public static function extract( string $navigation ) {
		$exploreChildURL = null;
		$exploreChildText = null;

		$forceExploreChildURL = null;
		$forceExploreChildText = null;

		if (
			ExtensionRegistry::getInstance()->isLoaded( 'Video' ) &&
			(
				strpos( $navigation, '{$NEWVIDEOS_CONDITIONAL}' ) !== false ||
				strpos( $navigation, '{$NEWVIDEOS}' ) !== false
			)
		) {
			$exploreChildURL = '**' . htmlspecialchars( Title::newFromText( 'NewVideos', NS_SPECIAL ) ) . '|';
			$exploreChildText = 'newvideos';

			if ( strpos( $navigation, '{$WANTEDPAGES_FORCE}' ) !== false ) {
				$forceExploreChildURL = "\n**" .
					htmlspecialchars( Title::newFromText( 'WantedPages', NS_SPECIAL ) ) . '|';
				$forceExploreChildText = 'wantedpages';
			}
		} elseif (
			strpos( $navigation, '{$WANTEDPAGES_CONDITIONAL}' ) !== false ||
			strpos( $navigation, '{$WANTEDPAGES}' ) !== false
		) {
			$exploreChildURL = '**' . htmlspecialchars( Title::newFromText( 'WantedPages', NS_SPECIAL ) ) . '|';
			$exploreChildText = 'wantedpages';
		}

		$cleanedMsg = preg_replace(
			'/(\{\$NEWVIDEOS\})|(\{\$WANTEDPAGES\})|(\{\$NEWVIDEOS_CONDITIONAL\})' .
				'|(\{\$WANTEDPAGES_CONDITIONAL\})|(\{\$WANTEDPAGES_FORCE\})/',
			'',
			$navigation
		);

		$message = trim(
			$cleanedMsg . $exploreChildURL . $exploreChildText . $forceExploreChildURL . $forceExploreChildText
		);

		if ( $message !== '' && $message !== '-' ) {
			$lines = explode( "\n", $message );

			if ( count( $lines ) > 0 ) {
				return $lines;
			}
		}

		return null;
	}
}
