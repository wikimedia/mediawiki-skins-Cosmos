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

if (!defined('MEDIAWIKI')) {
	die(-1);
}

class CosmosToolbar {

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
			$lines = self::getMessageAsArray('Cosmos-toolbar');
		}

		return $lines;
	}

	public function getMenu($lines) {
		$menu = '';
		$nodes = $this->parse($lines);

		if (count($nodes) > 0) {

			Hooks::run('getCosmosToolbar', array(&$nodes
			));

			$mainMenu = array();
			foreach ($nodes[0]['children'] as $key => $val) {
				$menu .= '<li id="' . Sanitizer::escapeIdForAttribute( 't-' . strtolower(strtr( $nodes[$val]['text'], ' ', '-' ) ) ) . '">';
				$menu .= '<a href="' . (!empty($nodes[$val]['href']) ? htmlspecialchars($nodes[$val]['href']) : '#') . '"';
				if (!isset($nodes[$val]['internal']) || !$nodes[$val]['internal']) $menu .= ' rel="nofollow"';
				$menu .= '><span>' . htmlspecialchars($nodes[$val]['text']) . '</span>';
				$menu .= '</a>';

			}
			$menu .= '</li>';
			
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
			elseif ($link[0] == '#') {
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
