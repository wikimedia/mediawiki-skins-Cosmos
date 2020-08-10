<?php
/**
 * CosmosNavigation class
 *
 * @package MediaWiki
 * @subpackage Skins
 *
 * @author Inez Korczynski <inez@wikia.com>
 * @author Christian Williams
 */
use Cosmos\Icon;

if(!defined('MEDIAWIKI')) {
	die(-1);
}

class CosmosNavigation {

	const version = '0.10';

	static function invalidateCache() {
		global $wgMemc;
		$wgMemc->delete(wfMemcKey('mCosmosNavigation', self::version));
		return true;
	}

	public $editUrl = false;

	/**
	 * Parse one line from MediaWiki message to array with indexes 'text' and 'href'
	 *
	 * @return array
	 * @author Inez Korczynski <inez@wikia.com>
	 */
	public static function parseItem($line) {

		$href = $specialCanonicalName = false;

		$line_temp = explode('|', trim($line, '* '), 3);
		$line_temp[0] = trim($line_temp[0], '[]');
		if(count($line_temp) >= 2 && $line_temp[1] != '') {
			$line = trim($line_temp[1]);
			$link = trim(wfMessage($line_temp[0])->inContentLanguage()->text());
		} else {
			$line = trim($line_temp[0]);
			$link = trim($line_temp[0]);
		}

		$descText = null;

		if(count($line_temp) > 2 && $line_temp[2] != '') {
			$desc = $line_temp[2];
			if ( wfMessage( $desc )->exists() ) {
				$descText = wfMessage( $desc )->text();
			} else {
				$descText = $desc;
			}
		}

		if ( wfMessage( $line )->exists() ) {
			$text = wfMessage( $line )->text();
		} else {
			$text = $line;
		}

		if($link != null) {
			if ( !wfMessage( $line_temp[0] )->exists() ) {
				$link = $line_temp[0];
			}
			if (preg_match( '/^(?:' . wfUrlProtocols() . ')/', $link )) {
				$href = $link;
			} else {
				$title = Title::newFromText( $link );
				if($title) {
					if ($title->getNamespace() == NS_SPECIAL) {
						$dbkey = $title->getDBkey();
						list( $specialCanonicalName, /*$par*/ ) = SpecialPageFactory::resolveAlias( $dbkey );
						if (!$specialCanonicalName) $specialCanonicalName = $dbkey;
					}
					$title = $title->fixSpecialName();
					$href = $title->getLocalURL();
				} else {
					$href = '#';
				}
			}
		}

		return array('text' => $text, 'href' => $href, 'org' => $line_temp[0], 'desc' => $descText, 'specialCanonicalName' => $specialCanonicalName);
	}

	/**
	 * @author Inez Korczynski <inez@wikia.com>
	 * @return array
	 */
	public static function getMessageAsArray($messageKey) {
        $message = trim(wfMessage($messageKey)->inContentLanguage()->text());
        if( wfMessage($messageKey, $message)->exists() ) {
                $lines = explode("\n", $message);
                if(count($lines) > 0) {
                        return $lines;
                }
        }
        return null;
	}

	public function getCode() {
		global $wgUser, $wgTitle, $wgRequest, $wgMemc, $wgLang, $wgContLang;

		$cache = $wgLang->getCode() == $wgContLang->getCode();
		if($cache) {
			$key = wfMemcKey('mCosmosNavigation', self::version);
			$menu = $wgMemc->get($key);
		}
		if(empty($menu)) {
			$menu = $this->getMenu($this->getMenuLines());
			if($cache) {
				$wgMemc->set($key, $menu, 60 * 60 * 8);
			}
		}
		return $menu;
	}

	public function getMenuLines() {
		if(empty($lines)) {
			$lines = CosmosNavigation::getMessageAsArray('Cosmos-navigation');
		}

		return $lines;
	}

	public function getSubMenu($nodes, $children) {
		$menu = '';
		foreach($children as $key => $val) {
			$link_html = htmlspecialchars($nodes[$val]['text']);
			if ( !empty( $nodes[$val]['children'] ) ) {
				$link_html .= Icon::getIcon( 'level-2-dropdown' )->makeSvg( 12, 12, [ 'id' => 'wds-icons-menu-control-tiny', 'class' => 'wds-icon wds-icon-tiny wds-dropdown-chevron' ] );
			}

			$menu_item =
				Html::rawElement( 'a', array(
						'href' => !empty($nodes[$val]['href']) ? $nodes[$val]['href'] : '#',
						'class' => (!empty( $nodes[$val]['children'] ) ? 'wds-dropdown-level-2__toggle' : null),
						'tabIndex' => 3,
						'rel' => $nodes[$val]['internal'] ? null : 'nofollow'
					), $link_html ) . "\n";
			if ( !empty( $nodes[$val]['children'] ) ) {
				$menu_item .= $this->getSubMenu( $nodes, $nodes[$val]['children'] );
				
			}
			$menu .=
				Html::rawElement( 'li', (!empty( $nodes[$val]['children'] ) ? array( "class" => "wds-is-sticked-to-parent wds-dropdown-level-2" ) : null), $menu_item );
		}
		$menu = Html::rawElement( 'div', array( 'class' => (!empty( $nodes[$val]['children'] ) ? 'wds-is-not-scrollable wds-dropdown-level-2__content' : 'wds-is-not-scrollable wds-dropdown-level-2__content') ), '<ul class="wds-list wds-is-linked' . (!empty( $nodes[$val]['children'] ) ? ' wds-has-bolded-items">' : '">') . $menu . '</ul>' );
		
		return $menu;
	}

	public function getMenu($lines, $userMenu = false) {
		global $wgMemc, $wgScript;
        $menu = '';
		$nodes = $this->parse($lines);

		if(count($nodes) > 0) {

			Hooks::run('CosmosNavigationGetMenu', array(&$nodes));

			$mainMenu = array();
			foreach($nodes[0]['children'] as $key => $val) {
				if(isset($nodes[$val]['children'])) {
					$mainMenu[$val] = $nodes[$val]['children'];
				}
				if(isset($nodes[$val]['magic'])) {
					$mainMenu[$val] = $nodes[$val]['magic'];
				}
				if(isset($nodes[$val]['href']) && $nodes[$val]['href'] == 'editthispage') $menu .= '<!--b-->';
				$menu .= '<li class="wds-tabs__tab">';
					if ( !empty($nodes[$val]['children']) || !empty($nodes[$val]['magic']) ) {
					$menu .= '<div class="wds-dropdown">';
				}
				$menu .= '<div class="wds-tabs__tab-label';
				if ( !empty($nodes[$val]['children']) || !empty($nodes[$val]['magic']) ) {
					$menu .= ' wds-dropdown__toggle';
				}
				$menu .= '">';
				$menu .= '<a href="'.(!empty($nodes[$val]['href']) ? htmlspecialchars($nodes[$val]['href']) : '#').'"';
				if ( !isset($nodes[$val]['internal']) || !$nodes[$val]['internal'] )
					$menu .= ' rel="nofollow"';
				$menu .= ' tabIndex=3><span>'.htmlspecialchars($nodes[$val]['text']) . '</span>';
				if ( !empty($nodes[$val]['children']) || !empty($nodes[$val]['magic']) ) {
					$menu .= Icon::getIcon( 'dropdown' )->makeSvg( 14, 14, [ 'id' => 'wds-icons-dropdown-tiny', 'class' => 'wds-icon wds-icon-tiny wds-dropdown__toggle-chevron' ] );
				}
				$menu .= '</a></div>';
				if ( !empty($nodes[$val]['children']) || !empty($nodes[$val]['magic']) ) {
				    $menu .= '<div class="wds-is-not-scrollable wds-dropdown__content">';
					$menu .= $this->getSubMenu($nodes, $nodes[$val]['children']);
					$menu .= '</div></div>';
				}
				
				if(isset($nodes[$val]['href']) && $nodes[$val]['href'] == 'editthispage') $menu .= '<!--e-->';
			}
	$menu .= '</li>';
			$classes = array();
			if ( $userMenu )
				$classes[] = 'userMenu';
			$classes[] = 'hover-navigation';
			
			$menu = Html::rawElement( 'li', array( 'class' => 'wds-tabs__tab'), $menu );

			if($this->editUrl) {
				$menu = str_replace('href="editthispage"', 'href="'.$this->editUrl.'"', $menu);
			} else {
				$menu = preg_replace('/<!--b-->(.*)<!--e-->/U', '', $menu);
			}

			if(isset($nodes[0]['magicWords'])) {
				$magicWords = $nodes[0]['magicWords'];
				$magicWords = array_unique($magicWords);
				sort($magicWords);
			}

			$menuHash = hash('md5', serialize($nodes));

			foreach($nodes as $key => $val) {
				if(!isset($val['depth']) || $val['depth'] == 1) {
					unset($nodes[$key]);
				}
				unset($nodes[$key]['parentIndex']);
				unset($nodes[$key]['depth']);
				unset($nodes[$key]['original']);
			}

			$nodes['mainMenu'] = $mainMenu;
			if(!empty($magicWords)) {
				$nodes['magicWords'] = $magicWords;
			}

			$wgMemc->set($menuHash, $nodes, 60 * 60 * 24 * 3); // three days

			// use AJAX request method to fetch JS code asynchronously
			//$menuJSurl = Xml::encodeJsVar("{$wgScript}?action=ajax&v=" . self::version. "&rs=getMenu&id={$menuHash}");
			//$menu .= "<script type=\"text/javascript\">/*<![CDATA[*/wsl.loadScriptAjax({$menuJSurl});/*]]>*/</script>";

			return $menu;
		}
	}

	public function parse($lines) {
		$nodes = array();
		$lastDepth = 0;
		$i = 0;
		if(is_array($lines) && count($lines) > 0) {
			foreach($lines as $line) {
				if(trim($line) === '') {
					continue; // ignore empty lines
				}

				$node = $this->parseLine($line);
				$node['depth'] = strrpos($line, '*') + 1;

				if($node['depth'] == $lastDepth) {
					$node['parentIndex'] = $nodes[$i]['parentIndex'];
				} else if ($node['depth'] == $lastDepth + 1) {
					$node['parentIndex'] = $i;
				} else {
					for($x = $i; $x >= 0; $x--) {
						if($x == 0) {
							$node['parentIndex'] = 0;
							break;
						}
						if($nodes[$x]['depth'] == $node['depth'] - 1) {
							$node['parentIndex'] = $x;
							break;
						}
					}
				}

				if($node['original'] == 'editthispage') {
					$node['href'] = 'editthispage';
					if($node['depth'] == 1) {
						$nodes[0]['editthispage'] = true; // we have to know later if there is editthispage special word used in first level
					}
				} else if(!empty( $node['original'] ) && $node['original']{0} == '#') {
					if($this->handleMagicWord($node)) {
						$nodes[0]['magicWords'][] = $node['magic'];
						if($node['depth'] == 1) {
							$nodes[0]['magicWord'] = true; // we have to know later if there is any magic word used if first level
						}
					} else {
						continue;
					}
				}

				$nodes[$i+1] = $node;
				$nodes[$node['parentIndex']]['children'][] = $i+1;
				$lastDepth = $node['depth'];
				$i++;
			}
		}
		return $nodes;
	}

	public function parseLine($line) {
		$lineTmp = explode('|', trim($line, '* '), 2);
		$lineTmp[0] = trim($lineTmp[0], '[]'); // for external links defined as [http://example.com] instead of just http://example.com

		$internal = false;

		if(count($lineTmp) == 2 && $lineTmp[1] != '') {
			$link = trim(wfMessage($lineTmp[0])->inContentLanguage()->text());
			$line = trim($lineTmp[1]);
		} else {
			$link = trim($lineTmp[0]);
			$line = trim($lineTmp[0]);
		}

		if ( wfMessage( $line )->exists() ) {
			$text = wfMessage( $line )->text();
		} else {
			$text = $line;
		}

		if ( !wfMessage( $lineTmp[0] )->exists() ) {
			$link = $lineTmp[0];
		}

		if(preg_match( '/^(?:' . wfUrlProtocols() . ')/', $link )) {
			$href = $link;
		} else {
			if(empty($link)) {
				$href = '#';
			} else if($link{0} == '#') {
				$href = '#';
			} else {
				$title = Title::newFromText($link);
				if(is_object($title)) {
					$href = $title->fixSpecialName()->getLocalURL();
					$internal = true;
				} else {
					$href = '#';
				}
			}
		}

		$ret = array('original' => $lineTmp[0], 'text' => $text);
		$ret['href'] = $href;
		$ret['internal'] = $internal;
		return $ret;
	}

	public function handleMagicWord(&$node) {
		$original_lower = strtolower($node['original']);
		if(in_array($original_lower, array('#voted#', '#popular#', '#visited#', '#newlychanged#', '#topusers#'))) {
			if($node['text']{0} == '#') {
				$node['text'] = wfMessage(trim($node['original'], ' *'))->text(); // TODO: That doesn't make sense to me
			}
			$node['magic'] = trim($original_lower, '#');
			return true;
		} else if(substr($original_lower, 1, 8) == 'category') {
			$param = trim(substr($node['original'], 9), '#');
			if(is_numeric($param)) {
				//$category = $this->getBiggestCategory($param);
				$name = $category['name'];
			} else {
				$name = substr($param, 1);
			}
			if($name) {
				$node['href'] = Title::makeTitle(NS_CATEGORY, $name)->getLocalURL();
				if($node['text']{0} == '#') {
					$node['text'] = str_replace('_', ' ', $name);
				}
				$node['magic'] = 'category'.$name;
				return true;
			}
		}
		return false;
	}

/*	private $biggestCategories;
	public function getBiggestCategory($index) {
		global $wgMemc, $wgBiggestCategoriesBlacklist;
		$limit = max($index, 15);
		if($limit > count($this->biggestCategories)) {
			$key = wfMemcKey('biggest', $limit);
			$data = $wgMemc->get($key);
			if(empty($data)) {
				$filterWordsA = array();
				foreach($wgBiggestCategoriesBlacklist as $word) {
					$filterWordsA[] = '(cl_to not like "%'.$word.'%")';
				}
				$dbr =& wfGetDB( DB_REPLICA );
				$tables = array("categorylinks");
				$fields = array("cl_to, COUNT(*) AS cnt");
				$where = count($filterWordsA) > 0 ? array(implode(' AND ', $filterWordsA)) : array();
				$options = array("ORDER BY" => "cnt DESC", "GROUP BY" => "cl_to", "LIMIT" => $limit);
				$res = $dbr->select($tables, $fields, $where, __METHOD__, $options);
				$categories = array();
				while ($row = $dbr->fetchObject($res)) {
					$this->biggestCategories[] = array('name' => $row->cl_to, 'count' => $row->cnt);
				}
				$wgMemc->set($key, $this->biggestCategories, 60 * 60 * 24 * 7);
			} else {
				$this->biggestCategories = $data;
			}
		}
		return isset($this->biggestCategories[$index-1]) ? $this->biggestCategories[$index-1] : null;
	}*/

}