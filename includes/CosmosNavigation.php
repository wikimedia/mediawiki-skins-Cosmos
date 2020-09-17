<?php
/**
 * CosmosNavigation class
 *
 * @package MediaWiki
 * @subpackage Skins
 *
 * @author Inez Korczynski <inez@wikia.com>
 * @author Christian Williams
 * @author Universal Omega
 */

if (!defined('MEDIAWIKI')) {
	die(-1);
}

use Cosmos\Icon;
class CosmosNavigation {

	/**
	 * Parse one line from MediaWiki message to array with indexes 'text' and 'href'
	 *
	 * @return array
	 * @author Inez Korczynski <inez@wikia.com>
	 */
	public static function parseItem($line) {

		$href = $specialCanonicalName = false;

		$line_temp = explode('|', trim($line, '* ') , 3);
		$line_temp[0] = trim($line_temp[0], '[]');
		if (count($line_temp) >= 2 && $line_temp[1] != '') {
			$line = trim($line_temp[1]);
			$link = trim(wfMessage($line_temp[0])->inContentLanguage()
				->text());
		}
		else {
			$line = trim($line_temp[0]);
			$link = trim($line_temp[0]);
		}

		$descText = null;

		if (count($line_temp) > 2 && $line_temp[2] != '') {
			$desc = $line_temp[2];
			if (wfMessage($desc)->exists()) {
				$descText = wfMessage($desc)->text();
			}
			else {
				$descText = $desc;
			}
		}

		if (wfMessage($line)->exists()) {
			$text = wfMessage($line)->text();
		}
		else {
			$text = $line;
		}

		if ($link != null) {
			if (!wfMessage($line_temp[0])->exists()) {
				$link = $line_temp[0];
			}
			if (preg_match('/^(?:' . wfUrlProtocols() . ')/', $link)) {
				$href = $link;
			}
			else {
				$title = Title::newFromText($link);
				if ($title) {
					if ($title->getNamespace() == NS_SPECIAL) {
						$dbkey = $title->getDBkey();
						list($specialCanonicalName) = SpecialPageFactory::resolveAlias($dbkey);
						if (!$specialCanonicalName) $specialCanonicalName = $dbkey;
					}
					$title = $title->fixSpecialName();
					$href = $title->getLocalURL();
				}
				else {
					$href = '#';
				}
			}
		}

		return array(
			'text' => $text,
			'href' => $href,
			'org' => $line_temp[0],
			'desc' => $descText,
			'specialCanonicalName' => $specialCanonicalName
		);
	}

	/**
	 * @author Inez Korczynski <inez@wikia.com>
	 * @return array
	 */
	public static function getMessageAsArray($messageKey) {
		$message = trim(wfMessage($messageKey)->inContentLanguage()
			->text());
		if (wfMessage($messageKey, $message)->exists()) {
			$lines = explode("\n", $message);
			if (count($lines) > 0) {
				return $lines;
			}
		}
		return null;
	}

	public function getCode() {

		if (empty($menu)) {
			$menu = $this->getMenu($this->getMenuLines());
		}
		return $menu;
	}

	public function getMenuLines() {
		if (empty($lines)) {
			$lines = CosmosNavigation::getMessageAsArray('Cosmos-navigation');
		}

		return $lines;
	}

	public function getSubMenu($nodes, $children) {
		$menu = '';
		$val = 0;
		foreach ($children as $key => $val) {
			$link_html = htmlspecialchars($nodes[$val]['text']);
			if (!empty($nodes[$val]['children'])) {
				$link_html .= Icon::getIcon('level-2-dropdown')->makeSvg(12, 12, ['id' => 'wds-icons-menu-control-tiny', 'class' => 'wds-icon wds-icon-tiny wds-dropdown-chevron']);
			}

			$menu_item = Html::rawElement('a', array(
				'href' => !empty($nodes[$val]['href']) ? $nodes[$val]['href'] : '#',
				'class' => (!empty($nodes[$val]['children']) ? 'wds-dropdown-level-2__toggle' : null) ,
				'tabIndex' => 3,
				'rel' => $nodes[$val]['internal'] ? null : 'nofollow'
			) , $link_html) . "\n";
			if (!empty($nodes[$val]['children'])) {
				$menu_item .= $this->getSubMenu($nodes, $nodes[$val]['children']);

			}
			$menu .= Html::rawElement('li', (!empty($nodes[$val]['children']) ? array(
				"class" => ($key > count($nodes[$val]['children']) - 1 ? 'wds-is-sticked-to-parent ' : '') . 'wds-dropdown-level-2'
			) : null) , $menu_item);
		}
		$menu = Html::rawElement('div', array(
			'class' => (!empty($nodes[$val]['children']) ? 'wds-is-not-scrollable wds-dropdown-level-2__content' : 'wds-is-not-scrollable wds-dropdown-level-2__content')
		) , '<ul class="wds-list wds-is-linked' . (!empty($nodes[$val]['children']) ? ' wds-has-bolded-items">' : '">') . $menu . '</ul>');

		return $menu;
	}

	public function getMenu($lines) {
		$menu = '';
		$nodes = $this->parse($lines);

		if (count($nodes) > 0) {

			Hooks::run('CosmosNavigationGetMenu', array(&$nodes
			));

			$mainMenu = array();

			foreach ($nodes[0]['children'] as $key => $val) {
				if (isset($nodes[$val]['children'])) {
					$mainMenu[$val] = $nodes[$val]['children'];
				}
				$menu .= '<li class="wds-tabs__tab">';
				if (!empty($nodes[$val]['children'])) {
					$menu .= '<div class="wds-dropdown">';
				}
				$menu .= '<div class="wds-tabs__tab-label';
				if (!empty($nodes[$val]['children'])) {
					$menu .= ' wds-dropdown__toggle';
				}
				$menu .= '">';
				$menu .= '<a href="' . (!empty($nodes[$val]['href']) && $nodes[$val]['text'] !== 'Navigation' ? htmlspecialchars($nodes[$val]['href']) : '#') . '"';
				if (!isset($nodes[$val]['internal']) || !$nodes[$val]['internal']) $menu .= ' rel="nofollow"';
				$menu .= ' tabIndex=3><span>' . htmlspecialchars($nodes[$val]['text']) . '</span>';
				if (!empty($nodes[$val]['children'])) {
					$menu .= Icon::getIcon('dropdown')->makeSvg(14, 14, ['id' => 'wds-icons-dropdown-tiny', 'class' => 'wds-icon wds-icon-tiny wds-dropdown__toggle-chevron']);
				}
				$menu .= '</a></div>';
				if (!empty($nodes[$val]['children'])) {
					$menu .= '<div class="wds-is-not-scrollable wds-dropdown__content">';
					$menu .= $this->getSubMenu($nodes, $nodes[$val]['children']);
					$menu .= '</div></div>';
				}

			}
			$menu .= '</li>';
			$classes = array();
			$classes[] = 'hover-navigation';

			$menu = Html::rawElement('li', array(
				'class' => 'wds-tabs__tab'
			) , $menu);
			$menu = preg_replace('/<!--b-->(.*)<!--e-->/U', '', $menu);

			$menuHash = hash('md5', serialize($nodes));

			foreach ($nodes as $key => $val) {
				if (!isset($val['depth']) || $val['depth'] == 1) {
					unset($nodes[$key]);
				}
				unset($nodes[$key]['parentIndex']);
				unset($nodes[$key]['depth']);
				unset($nodes[$key]['original']);
			}

			$nodes['mainMenu'] = $mainMenu;

			$memc = ObjectCache::getLocalClusterInstance();
			$memc->set($menuHash, $nodes, 60 * 60 * 24 * 3); // three days
			

			return $menu;
		}
	}

	public function parse($lines) {
		$nodes = array();
		$lastDepth = 0;
		$i = 0;
		if (is_array($lines) && count($lines) > 0) {
			foreach ($lines as $line) {
				if (trim($line) === '') {
					continue; // ignore empty lines
					
				}

				$node = $this->parseLine($line);
				$node['depth'] = strrpos($line, '*') + 1;

				if ($node['depth'] == $lastDepth) {
					$node['parentIndex'] = $nodes[$i]['parentIndex'];
				}
				elseif ($node['depth'] == $lastDepth + 1) {
					$node['parentIndex'] = $i;
				}
				else {
					for ($x = $i;$x >= 0;$x--) {
						if ($x == 0) {
							$node['parentIndex'] = 0;
							break;
						}
						if ($nodes[$x]['depth'] == $node['depth'] - 1) {
							$node['parentIndex'] = $x;
							break;
						}
					}
				}

				if (!empty($node['original']) && ($node['original'] == 'SEARCH' || $node['original'] == 'TOOLBOX' || $node['original'] == 'LANGUAGES')) {
					continue;
				}

				$nodes[$i + 1] = $node;
				$nodes[$node['parentIndex']]['children'][] = $i + 1;
				$lastDepth = $node['depth'];
				$i++;
			}
		}
		return $nodes;
	}

	public function parseLine($line) {
		$lineTmp = explode('|', trim($line, '* ') , 2);
		$lineTmp[0] = trim($lineTmp[0], '[]'); // for external links defined as [http://example.com] instead of just http://example.com
		$internal = false;

		if (count($lineTmp) == 2 && $lineTmp[1] != '') {
			$link = trim(wfMessage($lineTmp[0])->inContentLanguage()
				->text());
			$line = trim($lineTmp[1]);
		}
		else {
			$link = trim($lineTmp[0]);
			$line = trim($lineTmp[0]);
		}

		if (wfMessage($line)->exists()) {
			$text = wfMessage($line)->text();
		}
		else {
			$text = $line;
		}

		if (!wfMessage($lineTmp[0])->exists()) {
			$link = $lineTmp[0];
		}

		if (preg_match('/^(?:' . wfUrlProtocols() . ')/', $link)) {
			$href = $link;
		}
		else {
			if (empty($link)) {
				$href = '#';
			}
			else if ($link{0} == '#') {
				$href = '#';
			}
			else {
				$title = Title::newFromText($link);
				if (is_object($title)) {
					$href = $title->fixSpecialName()
						->getLocalURL();
					$internal = true;
				}
				else {
					$href = '#';
				}
			}
		}

		$ret = array(
			'original' => $lineTmp[0],
			'text' => $text
		);
		$ret['href'] = $href;
		$ret['internal'] = $internal;
		return $ret;
	}
}
