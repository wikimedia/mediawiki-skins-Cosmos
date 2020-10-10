<?php
/**
 * BaseTemplate class for the Cosmos skin
 *
 * @ingroup Skins
 */
use Cosmos\Config;
use Cosmos\Icon;
use MediaWiki\MediaWikiServices;

class CosmosTemplate extends BaseTemplate {
	/**
	 * Outputs the entire contents of the page
	 */
	public function execute() {
		$config = new Config();
		$skin = $this->getSkin();

		$this->getNotifications($this->data, $config);
		$html = $this->get('headelement');
		$this->buildBanner($html, $config);
		$html .= $this->buildCreateArticleDialog($html, $config);
		$html .= Html::openElement('div', ['id' => 'mw-content-container', 'class' => 'ts-container']);
		$html .= Html::openElement('div', ['id' => 'mw-content-block', 'class' => 'ts-inner']);
		$html .= Html::openElement('div', ['id' => 'mw-content-wrapper']);
		$html .= $this->buildWikiHeader($html, $config);
		$html .= $this->buildWiki($html, $config);
		$html .= Html::closeElement('div');
		$html .= Html::closeElement('div');
		$html .= Html::closeElement('div');
		$html .= Html::closeElement('div');
		
		$html .= $this->getTrail();
		$html .= Html::closeElement( 'body' );
		$html .= Html::closeElement( 'html' );
		$title = Title::newFromText($this->get('title'));
		if (class_exists('UserProfilePage') && ($config->isEnabled('profile-tags') || $config->isEnabled('show-editcount') || $config->isEnabled('allow-bio')) && (is_object($title) && ($title->getNamespace() == NS_USER || $title->getNamespace() == NS_USER_PROFILE) && !$title->isSubpage())) {
			// Set up Cosmos-specific SocialProfile Elements
			$profileOwner = Title::newFromText($this->get('title'))
				->getText();
			$parser = MediaWikiServices::getInstance()->getParser();
			$replace = [
				'<div id="profile-title">' . $profileOwner . '</div>',
				'<div id="profile-title-container">'
			];
			$replaceWith = [
				'<h1 itemprop="name">' . $profileOwner . '</h1>' . ($config->isEnabled('profile-tags') ? CosmosSocialProfile::usergroups($parser, $profileOwner) : '') . ($config->isEnabled('show-editcount') ? '<br/> <div class="contributions-details tally"><a href="' . htmlspecialchars(Title::newFromText("Contributions/$profileOwner", NS_SPECIAL)->getFullURL()) . '"><em>' . CosmosSocialProfile::useredits($parser, $profileOwner) . '</em><span>Edits since joining this wiki<br>' . CosmosSocialProfile::userregistration($parser, $profileOwner) . '</span></a></div>' : '') . ($config->isEnabled('allow-bio') ? CosmosSocialProfile::userbio($parser, $profileOwner) : '') ,
				'<div class="hgroup">'
			];
			return str_replace($replace, $replaceWith, $html);
		} else {
			return $html;
		}
	}

	protected function buildBanner(string & $html, Config $config) {
		$skin = $this->getSkin();
		// Open container section for banner
		$html .= Html::openElement('section', ['id' => 'cosmos-banner']);
		// Open container div for banner content
		$html .= Html::openElement('div', ['id' => 'cosmos-banner-content', 'class' => 'cosmos-pageAligned']);
		// Build the mobile navigation
		$html .= Html::openElement('nav', ['class' => 'cosmos-mobile-navigation']);
		$html .= Html::rawElement('div', ['class' => 'cosmos-button cosmos-button-primary cosmos-mobile-menu-button', 'onclick' => '$(".wds-tabs__tab").toggle()'], $this->getMsg('cosmos-mobile-menu-label')
			->text());
		$this->buildNavigation($html, $config);
		$html .= Html::closeElement('nav');
		// Build the logo
		$this->buildBannerLogo($html, $config);

		// Build the search bar
		$this->buildSearchBar($html, $config);

		// Build user options/login button (floats on the right of the div)
		$this->buildUserOptions($html, $config);

		// Close container div for banner content
		$html .= Html::closeElement('div');
		// Close banner section
		$html .= Html::closeElement('section');
	}
	protected function buildCreateArticleDialog(string & $html, Config $config) {
		$skin = $this->getSkin();
		$html .= Html::openElement('div', ['id' => 'createPageModal', 'class' => 'cosmos-modal']);
		$html .= Html::openElement('div', ['class' => 'cosmos-modal-content']);
		$html .= Html::rawElement('span', ['class' => 'close'], '&times;');
		$html .= Html::openElement('form', ['class' => 'wds-dialog__wrapper create-page-dialog__wrapper', 'action' => $this->get('wgScript') , 'method' => 'get']);
		$html .= Html::hidden('action', 'edit');
		$html .= Html::rawElement('header', ['class' => 'wds-dialog__title'], $this->getMsg('cosmos-createpage-header')
			->text() . Icon::getIcon('close')
			->makeSvg(14, 14, ['class' => 'wds-icon wds-icon-small create-page-dialog__close']));
		$html .= Html::openElement('div', ['class' => 'wds-dialog__content']);
		$html .= Html::rawElement('div', ['id' => 'create-page-dialog__message'], $this->getMsg('cosmos-createpage-input-label')
			->text());
		$html .= Html::openElement('div', ['class' => 'wds-input create-page-dialog__title-wrapper']);
		$html .= Html::input('title', '', 'text', ['class' => 'wds-input__field', 'id' => 'create-page-dialog__title']);
		$html .= Html::closeElement('div');
		$html .= Html::rawElement('div', ['id' => 'create-page-dialog__message'], $skin->msg('cosmos-createpage-dialoge-text', SiteStats::pagesInNs(0), $this->get('sitename')));
		$html .= Html::openElement('div', ['class' => 'create-page-dialog__proposals']);
		$html .= Html::openElement('ul', ['class' => 'articleProposals']);
		// Get most wanted pages
		foreach (self::getMostWantedPages() as $page) {
			$html .= '<li><a href="' . $page['url'] . '" class="new">' . $page['title'] . '</a></li>';
		}
		$html .= Html::closeElement('ul');
		$html .= Html::closeElement('div');
		$html .= Html::closeElement('div');
		$html .= Html::closeElement('br');
		$html .= Html::closeElement('br');
		$html .= Html::closeElement('br');
		$html .= Html::openElement('footer');
		$html .= Html::openElement('div', ['class' => 'wds-dialog__actions']);
		$html .= Html::openElement('input', ['type' => 'submit', 'class' => 'wds-button wds-is-text create-page-dialog__button', 'value' => $this->getMsg('cosmos-createpage-next')
			->text() , 'disabled', 'disabled']);
		$html .= Html::closeElement('div');
		$html .= Html::closeElement('footer');
		$html .= Html::closeElement('div');
		$html .= Html::closeElement('form');
		$html .= Html::closeElement('div');
		$html .= Html::closeElement('div');
	}
	protected static function getMostWantedPages() {
		$WantedPagesPageResponse = (new WantedPagesPage())->doQuery();
		$dbr = wfGetDB(DB_REPLICA);
		$wantedPages = [];
		$fetchedTitlesCount = 0;

		while ($row = $dbr->fetchObject($WantedPagesPageResponse)) {
			if ($row->title && in_array($row->namespace, [NS_MAIN]) && $fetchedTitlesCount < 6) {
				$wantedPageTitle = Title::newFromText($row->title, $row->namespace);

				if ($wantedPageTitle instanceof Title && !$wantedPageTitle->isKnown() && (empty('/[:\/]+/') || !preg_match('/[:\/]+/', $wantedPageTitle->getText()))) {
					$wantedPages[] = ['title' => $wantedPageTitle->getFullText() , 'url' => $wantedPageTitle->getLocalURL([static::getPreferredEditorQueryParamName() => 'edit', 'source' => 'redlink', ]) , ];
					$fetchedTitlesCount++;
				}
			}
		}

		return $wantedPages;
	}

	protected static function getPreferredEditorQueryParamName() {
		// todo, add veaction if visualeditor is the users default preference
		return 'action';
	}
	protected function buildNavigation(string & $html, Config $config) {
		global $wgManageWikiForceSidebarLinks, $wgManageWikiSidebarLinks, $wgManageWiki;
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		$cosmosNavigation = new CosmosNavigation();
		$skin = $this->getSkin();
		$append = '';
		$html .= Html::openElement('ul', ['class' => 'wds-tabs']);
		// Load site navigation links from MediaWiki:Cosmos-navigation
		$html .= $cosmosNavigation->getCode();
		// ManageWiki links
		if (ExtensionRegistry::getInstance()
			->isLoaded('ManageWiki') && in_array(true, $wgManageWiki, true) === true) {
			if ((!$permissionManager->userHasRight($skin->getUser(), 'managewiki')) && ($wgManageWikiForceSidebarLinks || $skin->getUser()
				->getOption('managewikisidebar', 1))) {
				$append = '-view';
			}
			if (($permissionManager->userHasRight($skin->getUser(), 'managewiki') || $wgManageWikiForceSidebarLinks || $skin->getUser()
				->getOption('managewikisidebar', 1)) && $wgManageWikiSidebarLinks !== false) {

				$html .= Html::rawElement('li', ['class' => 'wds-tabs__tab'], '<div class="wds-dropdown" id="p-' . Sanitizer::escapeIdForAttribute($this->getMsg('cosmos-administration')) . '" aria-labelledby="p-' . Sanitizer::escapeIdForAttribute($this->getMsg('cosmos-administration')) . '-label"><div class="wds-tabs__tab-label wds-dropdown__toggle" id="p-' . Sanitizer::escapeIdForAttribute($this->getMsg('cosmos-administration')) . '-label"><span style="padding-top: 2px;">' . $this->getMsg('cosmos-administration')
					->text() . '</span>' . Icon::getIcon('dropdown')
					->makeSvg(14, 14, ['id' => 'wds-icons-dropdown-tiny', 'class' => 'wds-icon wds-icon-tiny wds-dropdown__toggle-chevron']) . '</div><div class="wds-is-not-scrollable wds-dropdown__content"><ul class="wds-list wds-is-linked wds-has-bolded-items">');

				foreach ((array)ManageWiki::listModules() as $module) {
					$html .= "<li class='wds-tabs__tab'><a id='" . "managewiki{$module}link" . "' href='" . htmlspecialchars(SpecialPage::getTitleFor('ManageWiki', $module)->getFullURL()) . "'>" . wfMessage("managewiki-link-{$module}{$append}")->plain() . "</a></li>";
				}
				$html .= ('</div>');
			}
		}
		$html .= Html::closeElement('ul');
	}
	protected function buildBannerLogo(string & $html, Config $config) {
		// Open container div
		$html .= Html::openElement('div', ['id' => 'cosmos-banner-bannerLogo']);
		if ($config->getString('banner-logo')) {
			// Open link element
			$html .= Html::openElement('a', array_merge(['href' => $this->data['nav_urls']['mainpage']['href']], Linker::tooltipAndAccesskeyAttribs('p-logo')));

			// Insert logo image
			$html .= Html::rawElement('img', ['id' => 'cosmos-bannerLogo-image', 'src' => $config->getString('banner-logo') , 'alt' => $this->get('sitename') ]);

			// Close link element
			$html .= Html::closeElement('a');
		}
		// Close container div
		$html .= Html::closeElement('div');
	}
	/**
	 * Builds HTML code to present the user account-related options to the reader
	 * and appends it to the string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildUserOptions(string & $html, Config $config) {
		// Open container div
		$html .= Html::openElement('div', ['id' => 'cosmos-banner-userOptions']);
		if (!empty($this->data["username"])) {
			$this->buildNotifications($html, $config);
		}

		$this->buildPersonalTools($html, $config);

		// Close container div
		$html .= Html::closeElement('div');
	}

	protected function buildPersonalTools(string & $html, Config $config) {
		$skin = $this->getSkin();

		$html .= Html::openElement('div', ['id' => 'p-personal', 'class' => 'cosmos-userOptions-personalTools cosmos-dropdown cosmos-bannerOption', 'aria-labelledby' => 'p-personal-label']);

		$html .= Html::openElement('div', ['id' => 'cosmos-personalTools-userButton', 'class' => 'cosmos-dropdown-button cosmos-bannerOption-button']);

		if (class_exists('wAvatar') && $config->isEnabled('social-avatar')) {
			$avatar = new wAvatar($skin->getUser()
				->getId(), 'm');
			$avatarElement = $avatar->getAvatarURL();
		} else {
			$avatarElement = Icon::getIcon('avatar')->makeSvg(28, 28);
		}

		$html .= Html::rawElement('div', ['id' => 'cosmos-userButton-avatar', 'class' => 'cosmos-bannerOption-icon'], $avatarElement);

		$html .= Html::rawElement('span', ['id' => 'p-personal-label', 'class' => 'cosmos-userButton-label'], empty($this->data['username']) ? $skin->msg('cosmos-anonymous')
			->escaped() : $this->get('username'));

		$html .= Html::rawElement('div', ['id' => 'cosmos-userButton-icon', 'class' => 'cosmos-dropdown-icon cosmos-bannerOption-dropdownIcon'], Icon::getIcon('dropdown')->makeSvg(14, 14));

		$html .= Html::closeElement('div');
		$html .= Html::openElement('div', ['class' => 'body cosmos-personalTools-list cosmos-dropdown-list']);
		$html .= Html::openElement('ul');

		foreach ($this->data['personal_urls'] as $key => $item) {
			switch ($key) {
				case 'userpage':
					$item['text'] = $skin->msg('cosmos-personaltools-userpage')
						->escaped();
				break;
				case 'mytalk':
					$item['text'] = $skin->msg('cosmos-personaltools-usertalk')
						->escaped();
				break;
				case 'anontalk':
					$item['text'] = $skin->msg('cosmos-personaltools-anontalk')
						->escaped();
				break;
				default:
				break;
			}

			$tooltip = $skin->msg('tooltip-pt-' . $key);

			if (!empty($tooltip)) {
				$item['title'] = $tooltip->escaped();
			}
			// Don't build adminlinks into personal menu. If allowed, it will be built into the wiki header instead.
			// Don't build darkmode into personal menu, this skin does not support darkmode, so that would not do anything.
			// Don't build the notifications into the personal menu, they are built into the top banner instead.
			// to-do: convert to Skin::getPersonalToolsForMakeListItem (possibly)
			if ($key !== 'adminlinks' && $key !== 'darkmode-link' && $key !== 'notifications-alert' && $key !== 'notifications-notice') {
				// to-do: convert to Skin::makeListItem
				$html .= Html::rawElement('li', [
					'id' => 'pt-' . $key
				], Html::rawElement('a', [
					'class' => isset($item['class']) ? $item['class'] : false,
					'href' => $item['href'],
					'title' => $item['title']
				], $item['text']));
			}
		}
		$html .= Html::closeElement('ul');
		$html .= Html::closeElement('div');
		
		$html .= Html::closeElement('div');
	}

	protected static function getNotifications(array & $data, Config $config) {
		$data['cosmos_notifications'] = ['numNotifs' => 0, 'numMessages' => 0, 'notifs' => [], 'messages' => []];

		$numNotifs = & $data['cosmos_notifications']['numNotifs'];
		$numMessages = & $data['cosmos_notifications']['numMessages'];
		$notifs = & $data['cosmos_notifications']['notifs'];
		$messages = & $data['cosmos_notifications']['messages'];

		if (!empty($data['newtalk'])) {
			$messages[] = ['text' => Html::rawElement('div', [], $data['newtalk']) ];
			$numMessages++;
		}
	}
	// HACK: This function is inelegant, and should be refactored so that the
	//       construction of the icons and list is done by one function which is
	//       called multiple times, but supplied with different info
	protected function buildNotifications(string & $html, Config $config) {
		$skin = $this->getSkin();
		// Partial credits to the Timeless skin:
		if (ExtensionRegistry::getInstance()
			->isLoaded('Echo')) {
			// to-do: convert to Skin::getPersonalToolsForMakeListItem
			$personalTools = $this->getPersonalTools();
			$notificationIcons = [];
			$notificationIcons['notifications-alert'] = $personalTools['notifications-alert'];
			$notificationIcons['notifications-notice'] = $personalTools['notifications-notice'];

			if (!empty($notificationIcons)) {
				$iconList = '';
				foreach ($notificationIcons as $key => $item) {
					// to-do: convert to Skin::makeListItem
					$iconList .= $this->makeListItem($key, $item);
				}

				$html .= Html::rawElement('div', ['id' => 'cosmos-notification-icons'], Html::rawElement('div', ['id' => 'cosmos-notifsButton-icon', 'class' => 'cosmos-bannerOption-icon'], $iconList));
			}
		} else {
			$html .= Html::openElement('div', ['id' => 'cosmos-userOptions-notifications', 'class' => 'cosmos-dropdown cosmos-bannerOption']);

			$html .= Html::openElement('div', ['id' => 'cosmos-notifications-notifsButton', 'class' => 'cosmos-dropdown-button cosmos-bannerOption-button']);

			$html .= Html::rawElement('div', ['id' => 'cosmos-notifsButton-icon', 'class' => 'cosmos-bannerOption-icon'], Icon::getIcon('notification')->makeSvg(28, 28));

			$html .= Html::rawElement('div', ['id' => 'cosmos-notifsButton-icon', 'class' => 'cosmos-dropdown-icon cosmos-bannerOption-dropdownIcon'], Icon::getIcon('dropdown')->makeSvg(14, 14));

			if ($this->data['cosmos_notifications']['numNotifs'] > 0) {
				$html .= Html::element('div', ['id' => 'cosmos-notifsButton-numNotifs', 'class' => 'cosmos-notifications-numNotifs'], $this->data['cosmos_notifications']['numNotifs']);
			}

			$html .= Html::closeElement('div');

			$html .= Html::openElement('ul', ['id' => 'cosmos-notifications-list', 'class' => 'cosmos-dropdown-list']);

			if ($this->data['cosmos_notifications']['numNotifs'] > 0) {
				foreach ($this->data['cosmos_notifications']['notifs'] as $notif) {
					$html .= Html::openElement('li');

					if (!empty($notif['href'])) {
						$html .= Html::openElement('a', ['href' => $notif['href']]);
					}

					$html .= $notif['text'];

					if (!empty($notif['href'])) {
						$html .= Html::closeElement('a');
					}

					$html .= Html::closeElement('li');
				}
			} else {
				$html .= Html::openElement('li', ['class' => 'cosmos-emptyListMessage']);

				$html .= Html::element('div', [], $skin->msg('cosmos-notifications-nonotifs'));

				$html .= Html::closeElement('li');
			}

			$html .= Html::closeElement('ul');

			$html .= Html::closeElement('div');

			$html .= Html::openElement('div', ['id' => 'cosmos-userOptions-messages', 'class' => 'cosmos-dropdown cosmos-bannerOption']);

			$html .= Html::openElement('div', ['id' => 'cosmos-messages-messagesButton', 'class' => 'cosmos-dropdown-button cosmos-bannerOption-button']);

			$html .= Html::rawElement('div', ['id' => 'cosmos-messagesButton-icon', 'class' => 'cosmos-bannerOption-icon'], Icon::getIcon('message')->makeSvg(28, 28));

			$html .= Html::rawElement('div', ['id' => 'cosmos-messagesButton-icon', 'class' => 'cosmos-dropdown-icon cosmos-bannerOption-dropdownIcon'], Icon::getIcon('dropdown')->makeSvg(14, 14));

			if ($this->data['cosmos_notifications']['numMessages'] > 0) {
				$html .= Html::element('div', ['id' => 'cosmos-messagesButton-numMessages', 'class' => 'cosmos-notifications-numNotifs'], $this->data['cosmos_notifications']['numMessages']);
			}

			$html .= Html::closeElement('div');

			$html .= Html::openElement('ul', ['id' => 'cosmos-messages-list', 'class' => 'cosmos-dropdown-list']);

			if ($this->data['cosmos_notifications']['numMessages'] > 0) {
				foreach ($this->data['cosmos_notifications']['messages'] as $message) {
					$html .= Html::openElement('li');

					if (!empty($message['href'])) {
						$html .= Html::openElement('a', ['href' => $message['href']]);
					}

					$html .= $message['text'];

					if (!empty($message['href'])) {
						$html .= Html::closeElement('a');
					}

					$html .= Html::closeElement('li');
				}
			} else {
				$html .= Html::openElement('li', ['class' => 'cosmos-emptyListMessage']);

				$html .= Html::rawElement('div', [], $skin->msg('cosmos-notifications-nomessages'));

				$html .= Html::closeElement('li');
			}

			$html .= Html::closeElement('ul');

			$html .= Html::closeElement('div');
		}
	}

	/**
	 * Builds HTML code to present the search form to the user, and appends it to
	 * string passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildSearchBar(string & $html, Config $config) {
		// Open container div
		$html .= Html::openElement('div', ['id' => 'cosmos-banner-search']);

		// Open search form
		$html .= Html::openElement('form', ['action' => $this->get('wgScript') , 'id' => 'cosmos-search-form']);

		// Insert hidden search title
		$html .= Html::element('input', ['type' => 'hidden', 'name' => 'title', 'value' => $this->get('searchtitle') ]);

		// Insert search bar
		$html .= $this->makeSearchInput(['id' => 'searchInput', 'class' => 'cosmos-search-input']);

		$html .= Html::openElement('div', ['id' => 'cosmos-search-buttonContainer', 'class' => 'cosmos-bannerOption-button']);

		// Insert search icon
		$html .= Html::rawElement('div', ['id' => 'cosmos-search-buttonIcon', 'class' => 'cosmos-bannerOption-icon'], Icon::getIcon('search')->makeSvg(28, 28));

		// Insert search button
		$html .= $this->makeSearchButton('go', ['id' => 'searchButton', 'class' => 'cosmos-search-button']);

		// Insert fallback search button
		$html .= $this->makeSearchButton('fulltext', ['id' => 'mw-searchButton', 'class' => 'mw-fallbackSearchButton cosmos-search-button']);

		$html .= Html::closeElement('div');

		// Close form
		$html .= Html::closeElement('form');

		// Close container div
		$html .= Html::closeElement('div');
	}

	protected function buildWikiHeader(string & $html, Config $config) {
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		$skin = $this->getSkin();
		$html .= Html::openElement('header', ['class' => 'cosmos-header', 'style' => $config->getString('header-background') ? "background-image: url({$config->getString('header-background') });" : null]);
		$this->buildWordmark($html, $config);
		$html .= Html::openElement('div', ['class' => 'cosmos-header__top-container']);
		$html .= Html::openElement('div', ['class' => 'cosmos-header__sitename']);
		$html .= Html::rawElement('a', ['href' => $this->data['nav_urls']['mainpage']['href']], $this->getMsg('cosmos-tagline')
			->text());
		$html .= Html::closeElement('div');
		$html .= Html::openElement('div', ['class' => 'cosmos-header__counter']);
		$html .= Html::rawElement('span', ['class' => 'cosmos-header__counter-value'], SiteStats::pagesInNs(0));
		$html .= Html::rawElement('span', ['class' => 'cosmos-header__counter-label'], $this->getMsg('cosmos-counter-label', SiteStats::pagesInNs(0))
			->text());
		$html .= Html::closeElement('div');
		$html .= Html::openElement('div', ['class' => 'cosmos-header__wiki-buttons wds-button-group']);
		$html .= Html::rawElement('a', ['class' => 'wds-button wds-is-secondary createpage', 'id' => 'createpage', 'href' => '#create-article', 'data-tracking' => 'add-new-page', 'title' => $this->getMsg('Cosmos-add-new-page-title')
			->text() ], Icon::getIcon('newpage')
			->makeSvg(1000, 1000, ['class' => 'wds-icon wds-icon-small', 'id' => 'wds-icons-page-small', 'style' => ($permissionManager->userHasRight($skin->getUser(), 'adminlinks') ? 'margin-right: 0;' : '') ]) . (!$permissionManager->userHasRight($skin->getUser(), 'adminlinks') ? (empty($this->data["username"]) ? $this->getMsg("Cosmos-anon-add-new-page-text")
			->text() : $this->getMsg("Cosmos-add-new-page-text")
			->text()) : ''));
		if (!empty($this->data["username"])) {
			$html .= Html::rawElement('a', ['class' => 'wds-button wds-is-secondary', 'href' => htmlspecialchars(Title::newFromText('RecentChanges', NS_SPECIAL)->getFullURL()) , 'data-tracking' => 'recent-changes', 'title' => $this->getMsg('Cosmos-recentchanges')
				->text() ], Icon::getIcon('recentchanges')
				->makeSvg(22, 22, ['class' => 'wds-icon-small', 'id' => 'wds-icons-activity-small', 'stroke' => 'currentColor', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'stroke-width' => 2]));
			if (!empty($this->data['username']) && $permissionManager->userHasRight($skin->getUser(), 'adminlinks')) {
				$html .= Html::rawElement('a', ['class' => 'wds-button wds-is-secondary', 'href' => htmlspecialchars(Title::newFromText('AdminLinks', NS_SPECIAL)->getFullURL()) , 'data-tracking' => 'admin-links', 'title' => $this->getMsg('Cosmos-adminlinks')
					->text() ], Icon::getIcon('admindashboard')
					->makeSvg(24, 24, ['class' => 'wds-icon-small', 'id' => 'wds-icons-dashboard-small']));
			}
			$html .= Html::rawElement('div', ['class' => 'wds-dropdown'], '<div class="wds-button wds-is-secondary wds-dropdown__toggle">' . Icon::getIcon('more')->makeSvg(384, 384, ['class' => 'wds-icon wds-icon-small', 'id' => 'wds-icons-more']) . '</div><div class="wds-dropdown__content wds-is-not-scrollable wds-is-right-aligned"><ul class="wds-list wds-is-linked"><li><a href="' . htmlspecialchars(Title::newFromText("Upload", NS_SPECIAL)
				->getFullURL()) . '" data-tracking="more-upload-file">' . $this->getMsg('cosmos-upload')
				->text() . '</a></li><li><a href="' . htmlspecialchars(Title::newFromText('RecentChanges', NS_SPECIAL)
				->getFullURL()) . '" data-tracking="more-recent-changes">' . $this->getMsg('Cosmos-recentchanges')
				->text() . '</a></li></ul></div>');
		}
		$html .= Html::closeElement('div');
		$html .= Html::closeElement('div');
		$html .= Html::openElement('nav', ['class' => 'cosmos-header__local-navigation']);
		$this->buildNavigation($html, $config);
		$html .= Html::closeElement('nav');
		$html .= Html::closeElement('header');
	}

	protected function buildWordmark(string & $html, Config $config) {
		if ($config->getString('header-wordmark')) {
			// Open container div for logo
			$html .= Html::openElement('div', ['class' => 'cosmos-header__wordmark']);

			// Open link element
			$html .= Html::openElement('a', array_merge(['href' => $this->data['nav_urls']['mainpage']['href']], Linker::tooltipAndAccesskeyAttribs('p-logo')));

			// Insert logo image
			$html .= Html::rawElement('img', ['src' => $config->getString('header-wordmark') , 'alt' => $this->get('sitename') ]);

			// Close link element
			$html .= Html::closeElement('a');

			// Close container div
			$html .= Html::closeElement('div');
		}
	}

	/**
	 * Generate the page content block
	 * Broken out here due to the excessive indenting, or stuff.
	 *
	 * @return string html
	 */
	protected function BuildWiki(string & $html, Config $config) {
		// Open container element for page body (i.e. actual content such as the
		// article and the sidebar)
		$html .= Html::openElement('section', ['id' => 'mw-content']);
		$html .= Html::openElement('div', ['id' => 'content', 'class' => 'cosmos-pageAligned mw-body']);
		// Build the header
		$this->buildHeader($html, $config);
		$html .= Html::openElement('div', ['class' => 'cosmos-articleContainer']);
		// Build the article content
		$this->buildArticle($html, $config);
		// Build the sidebars
		$this->buildRail($html, $config);
		
		$html .= Html::closeElement('div');

		// Close container element for page body
		$html .= Html::closeElement('div');
		$html .= Html::closeElement('section');

		$this->buildFooter($html, $config);
		$this->buildToolbar($html, $config);

		// Close container element for page
	}
	protected function buildRail(string & $html, Config $config) {
		if (($this->getMsg('cosmos-customsidebar')
			->text() !== '-' && $this->getMsg('cosmos-customsidebar')
			->text() !== '' && $this->getMsg('cosmos-customsidebar')
			->exists()) || ($this->getMsg('cosmos-stickysidebar')
			->text() !== '-' && $this->getMsg('cosmos-stickysidebar')
			->text() !== '' && $this->getMsg('cosmos-stickysidebar')
			->exists())) {
			$html .= Html::openElement('div', ['class' => 'CosmosRail', 'id' => 'CosmosRailWrapper']);
			$html .= Html::openElement('div', ['class' => 'cosmos-rail-inner loaded', 'id' => 'CosmosRail']);
			if ($this->getMsg('cosmos-customsidebar')
			->text() !== '-' && $this->getMsg('cosmos-customsidebar')
			->text() !== '' && $this->getMsg('cosmos-customsidebar')
			->exists()) {
				$html .= Html::openElement('section', ['class' => 'railModule module']);
				$html .= $this->getMsg('cosmos-customsidebar')->parse();
				$html .= Html::closeElement('section');
			}
			if ($this->getMsg('cosmos-stickysidebar')
			->text() !== '-' && $this->getMsg('cosmos-stickysidebar')
			->text() !== '' && $this->getMsg('cosmos-stickysidebar')
			->exists()) {
				$html .= Html::openElement('section', ['class' => 'railModule module rail-sticky-module']);
				$html .= $this->getMsg('cosmos-stickysidebar')->parse();
				$html .= Html::closeElement('section');
			}
			$html .= Html::closeElement('div');
			$html .= Html::closeElement('div');
		}
	}

	protected function buildHeader(string & $html, Config $config) {
		// Open container element for header
		$html .= Html::openElement('header', ['id' => 'cosmos-page-header']);

		// Build article header
		$this->buildArticleHeader($html, $config);

		// Close container element
		$html .= Html::closeElement('header');
	}
	protected function buildArticle(string & $html, Config $config) {
		// Open container element for article
		$html .= Html::openElement('article', ['id' => 'cosmos-pageBody-content']);

		// If it exists, insert the page subtitle
		if (!empty($this->data['subtitle'])) {
			$html .= Html::rawElement('div', ['id' => 'cosmos-pageContent-subtitle'], $this->get('subtitle'));
		}

		// If it exists, insert the article undelete message
		if (!empty($this->data['undelete'])) {
			$html .= Html::rawElement('div', ['id' => 'cosmos-pageContent-undelete'], $this->get('undelete'));
		}
		// If it exists, display the site notice at the top of the article
		// Check for dissmissable site notice extension
		$request = new WebRequest;

		if (ExtensionRegistry::getInstance()->isLoaded('DismissableSiteNotice')) {
			$html .= $this->get('sitenotice');
		} elseif (!empty($this->data['sitenotice']) && (!$request->getCookie("CosmosSiteNoticeState") || $request->getCookie("CosmosSiteNoticeState") !== 'closed')) {
			$html .= Html::openElement('div', ['id' => 'cosmos-content-siteNotice', 'data-site-notice-hash' => hash('crc32b', $this->get('sitenotice')) ]);

			// Display the site notice close button
			$html .= Html::rawElement('div', ['class' => 'cosmos-button cosmos-button-primary', 'id' => 'cosmos-siteNotice-closeButton'], Icon::getIcon('close')->makeSvg(14, 14, ['id' => 'cosmos-siteNotice-closeIcon']));

			$html .= $this->get('sitenotice');

			$html .= Html::closeElement('div');
		}
		$html .= $this->get('bodytext');

		// If appropriate, insert the category links at the bottom of the page
		if (!empty($this->data['catlinks'])) {
			$html .= Html::rawElement('span', ['id' => 'cosmos-content-categories'], $this->get('catlinks'));
		}

		// If there is any additional data or content to show, insert it now
		if (!empty($this->data['dataAfterContent'])) {
			$html .= Html::rawElement('span', ['id' => 'cosmos-content-additionalContent'], $this->get('dataAfterContent'));
		}

		// Close container element for article
		$html .= Html::closeElement('article');
	}
	protected function buildArticleHeader(string & $html, Config $config) {
		$html .= Html::openElement('div', ['id' => 'cosmos-header-articleHeader']);
		$html .= Html::openElement('h1', ['id' => 'cosmos-articleHeader-title', 'class' => 'firstHeading']);
		$html .= Html::rawElement('span', ['id' => 'cosmos-title-text'], $this->get('title'));
		$html .= $this->getIndicators();
		$html .= Html::closeElement('h1');
		$html .= Html::openElement('div', ['id' => 'cosmos-articleHeader-actions']);
		$this->buildActionButtons($html, $config);
		$html .= Html::closeElement('div');
		$html .= Html::closeElement('div');
	}
	protected function buildActionButtons(string & $html, Config $config) {
		$skin = $this->getSkin();
		$title = $skin->getRelevantTitle();
		$talkTitle = empty($title) ? null : $title->getTalkPageIfDefined();
		$isEditing = false;
		$isViewSource = false;
		$isHistory = false;
		$isSpecialAction = false;
		$isTalkPage = !empty($title) ? $title->isTalkPage() : false;
		$view = null;
		$edit = null;
		$talk = null;
		$dropdown = [];

		// Sort through the flat content actions array provided by the API, and
		// extract, discard and modify what is necessary
		foreach ($this->data['content_actions'] as $key => $tab) {
			switch ($key) {
					// If the action is edit or view source, assign the tab array to the
					// edit variable, and specify the path to the image to use as the
					// button's icon
					
				case 'edit':
					$edit = $tab;
					$edit['imgType'] = 'svg';
					$edit['imgSrc'] = 'edit';
					if (stripos($tab['class'], 'selected') !== false) {
						$isEditing = true;
					}
				break;
				case 'viewsource':
					$edit = $tab;
					$edit['imgType'] = 'svg';
					$edit['imgSrc'] = 'view';
					if (stripos($tab['class'], 'selected') !== false) {
						$isViewSource = true;
					}
				break;
					// If the action is talk, assign the tab array to the talk variable and
					// specify the path to the button icon
					
				case 'talk':
					$talk = $tab;
					$talk['text'] = $skin->msg('cosmos-action-talk')
						->escaped();
					$talk['imgType'] = 'svg';
					$talk['imgSrc'] = 'talk';
				break;
					// If the action is add section, then replace the tooltip (which is, by
					// default, just a '+') with 'Add new section', a more appropriate
					// message for a drop-down list format and then DELIBERATELY fall
					// through to the default case
					
				case 'addsection':
					$tab['text'] = $skin->msg('cosmos-action-addsection')
						->escaped();
					// Finally, if the content action is none of the above, add it to the
					// growing array of miscellaneous content actions to be displayed in a
					// drop-down list beneath the edit/view soure button
					
				default:
					if (substr($key, 0, 6) === 'nstab-') {
						$view = $tab;
					} else {
						if (stripos($tab['class'], 'selected') === false) {
							$dropdown[$key] = $tab;
						} else {
							if ($key === 'history') {
								$isHistory = true;
							} else {
								$isSpecialAction = true;
							}
						}
					}
				break;
			}
		}

		// Add Cosmos-specific classes to the view, edit and talk buttons
		if (!empty($view)) {
			$view['class'] .= ' cosmos-actions-view';
		}
		if (!empty($edit)) {
			$edit['class'] .= ' cosmos-actions-edit';
		}
		if (!empty($talk)) {
			$talk['class'] .= ' cosmos-actions-talk';
		}

		$primary = '';
		$secondary = '';
		if ($isEditing || $isSpecialAction) {
			if ($isTalkPage) {
				// Primary button leads back to talk page
				if (!empty($talk)) {
					$talk['imgType'] = 'svg';
					$talk['imgSrc'] = 'cancel';
					$talk['text'] = $skin->msg('cosmos-action-cancel')
						->escaped();
					// Set href to the talk URL, so that if the talk page doesn't exist,
					// clicking the button while editing it doesn't use the redlink URL
					// that would take the user straight back to edit page
					if (!empty($talkTitle)) {
						$talk['href'] = $talkTitle->getLinkURL();
					}
					$primary = $talk;
				}
				// Secondary button leads back to article
				if (!empty($view)) {
					$view['imgType'] = 'svg';
					$view['imgSrc'] = 'back';
					$view['text'] = $skin->msg('cosmos-action-backtopage', $view['text'])->escaped();
					$secondary = $view;
				}
			} else {
				// Primary button leads back to article
				if (!empty($view)) {
					$view['imgType'] = 'svg';
					$view['imgSrc'] = 'cancel';
					$view['text'] = $skin->msg('cosmos-action-cancel')
						->escaped();
					// Set href to the page URL, so that if the page doesn't exist,
					// clicking the button while editing it doesn't use the redlink URL
					// that would take the user straight back to edit page
					if (!empty($title)) {
						$view['href'] = $title->getLinkURL();
					}
					$primary = $view;
				}
				// Secondary button leads to talk page
				if (!empty($talk)) {
					$talk['imgType'] = 'svg';
					$talk['imgSrc'] = 'talk';
					$secondary = $talk;
				}
			}
			// Edit pushed to dropdown
			if (!$isEditing && !empty($edit)) {
				array_unshift($dropdown, $edit);
			}
		} elseif ($isHistory || $isViewSource) {
			if ($isTalkPage) {
				// Primary button leads back to talk page
				if (!empty($talk)) {
					$talk['imgType'] = 'svg';
					$talk['imgSrc'] = 'back';
					$talk['text'] = $skin->msg('cosmos-action-back')
						->escaped();
					$primary = $talk;
				}
				// Secondary button leads back to article
				if (!empty($view)) {
					$view['imgType'] = 'svg';
					$view['imgSrc'] = 'back';
					$view['text'] = $skin->msg('cosmos-action-backtopage', $view['text'])->escaped();
					$secondary = $view;
				}
			} else {
				// Primary button leads back to article
				if (!empty($view)) {
					$view['imgType'] = 'svg';
					$view['imgSrc'] = 'back';
					$view['text'] = $skin->msg('cosmos-action-back')
						->escaped();
					$primary = $view;
				}
				// Secondary button leads to talk page
				if (!empty($talk)) {
					$secondary = $talk;
				}
			}
			// Edit pushed to dropdown
			if (!$isViewSource && !empty($edit)) {
				array_unshift($dropdown, $edit);
			}
		} else {
			if ($isTalkPage) {
				// Primary button leads to talk page edit
				if (!empty($edit)) {
					$primary = $edit;
				}
				// Secondary button leads back to article
				if (!empty($view)) {
					$view['imgType'] = 'svg';
					$view['imgSrc'] = 'back';
					$view['text'] = $skin->msg('cosmos-action-backtopage', $view['text'])->escaped();
					$secondary = $view;
				}
			} else {
				// Primary button leads to article edit
				if (!empty($edit)) {
					$primary = $edit;
				}
				// Secondary button leads to talk page
				if (!empty($view)) {
					$secondary = $talk;
				}
			}
		}

		// Add Cosmos-specific classes to the primary and secondary buttons
		if (!empty($primary)) {
			$primary['class'] .= ' cosmos-button cosmos-button-primary cosmos-button-action';
		}
		if (!empty($secondary)) {
			$secondary['class'] .= ' cosmos-button cosmos-button-secondary cosmos-button-action';
		}

		// If the primary content action is available, display it as a button
		if (!empty($primary) && $primary !== null) {
			$this->buildActionButton($html, $config, $primary);
		}

		// If there are one or more miscellaneous content actions available,
		// display them as a drop-down list following the primary button
		if (count($dropdown) > 0) {
			$this->buildActionDropdown($html, $config, $dropdown);
		}

		// If the secondary content action is available, display it as a button
		if (!empty($secondary) && $secondary !== null) {
			$this->buildActionButton($html, $config, $secondary);
		}
	}

	/**
	 * Builds HTML code to for an individual content action button, and appends
	 * it to the string passed
	 *
	 * @param $html string The string onto which the HTML should be appended
	 * @param $info array An array with the necessary info to build the button
	 */
	protected function buildActionButton(string & $html, Config $config, array $info) {
		// If the button links to another page, surround it in an <a> element that
		// links there
		if (!empty($info['href'])) {
			$html .= Html::openElement('a', ['href' => $info['href'], 'title' => $info['title']??'']);
		}

		// Open a <div> for the button
		$html .= Html::openElement('div', ['id' => $info['id'], 'class' => $info['class']]);

		if (isset($info['imgSrc'])) {
			// If the button is to have an icon, display the icon in the format
			// corresponding to the given image type
			switch ($info['imgType']) {
				case 'svg':
					$icon = Icon::getIcon($info['imgSrc']);
					if (!isset($icon)) {
						break;
					}
					$html .= $icon->makeSvg(28, 28, ['class' => 'cosmos-button-icon']);
				break;
				default:
					$stylePath = $this->getSkin()
						->getConfig()
						->get('StylePath');
					$html .= Html::rawElement('img', ['src' => $stylePath . '/Cosmos/resources/icons/' . $info['imgSrc']]);
				break;
			}
		}

		// Place the button text in a <span> element
		$html .= Html::rawElement('span', ['class' => 'cosmos-button-text'], $info['text']);

		// Close the main button <div> element
		$html .= Html::closeElement('div');

		// If necessary, close the <a> element surrounding the button too
		if (isset($info['href'])) {
			$html .= Html::closeElement('a');
		}
	}

	/**
	 * Builds HTML code to for a drop-down list of selectable content actions,
	 * and appends it to a given string
	 *
	 * @param $html string The string onto which the HTML should be appended
	 * @param $info array An array of items which should be placed in the list
	 */
	protected function buildActionDropdown(string & $html, Config $config, array $items) {
		// Open a <div> element to contain the entire drop-down
		$html .= Html::openElement('div', ['class' => 'cosmos-dropdown', 'id' => 'cosmos-actions-actionsList']);

		// Open a div for a button that will display the list when hovered over
		// (this is achieved via CSS styling of the cosmos-dropdown,
		// cosmos-dropdown-button, cosmos-dropdown-icon and cosmos-dropdown-list classes)
		$html .= Html::openElement('div', ['class' => 'cosmos-button cosmos-button-primary cosmos-button-action ' . 'cosmos-dropdown-button', 'id' => 'cosmos-actionsList-button']);

		// Insert the dropdown icon
		$html .= Html::rawElement('div', ['id' => 'cosmos-actionsList-dropdownIcon', 'class' => 'cosmos-dropdown-icon'], Icon::getIcon('dropdown')->makeSvg(14, 14));

		// Close the button div
		$html .= Html::closeElement('div');

		// Open an <ul> element to contain the list itself
		$html .= Html::openElement('ul', ['class' => 'cosmos-dropdown-list', 'id' => 'cosmos-actionsList-list']);

		// Step through the array and use the makeListItem to convert each of the
		// items into a properly formatted HTML <li> element
		foreach ($items as $key => $value) {
			// to-do: convert to Skin::makeListItem
			$html .= $this->makeListItem($key, $value);
		}

		// Close the <ul> list container
		$html .= Html::closeElement('ul');

		// Close the <div> container
		$html .= Html::closeElement('div');
	}

	//////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	////////////////////////                              ////////////////////////
	////////////////////////            FOOTER            ////////////////////////
	////////////////////////                              ////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	
	/**
	 * Builds HTML code for the page foooter, and appends it to the string passed
	 * to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildFooter(string & $html, Config $config) {
		// Open container element for footer
		$html .= Html::openElement('footer', ['id' => 'cosmos-footer']);

		// Open container element for footer content
		$html .= Html::openElement('div', ['class' => 'cosmos-pageAligned']);

		$html .= Html::openElement('div', ['id' => 'cosmos-footer-footerContent']);

		$html .= Html::openElement('div', ['class' => 'cosmos-articleContainer']);

		// Build the footer links
		$this->buildFooterLinks($html, $config);

		// Build the footer icons
		$this->buildFooterIcons($html, $config);

		$html .= Html::closeElement('div');

		$html .= Html::closeElement('div');

		// Close container element for footer content
		$html .= Html::closeElement('div');

		// Close container element for footer
		$html .= Html::closeElement('footer');
	}

	/**
	 * Builds HTML code to display the footer icons, and appends it to the string
	 * that is passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildFooterIcons(string & $html, Config $config) {
		$footerIcons = $this->get('footericons');
		$skin = $this->getSkin();
		if (count($footerIcons) > 0) {
			// Open container div for icons
			$html .= Html::openElement('div', ['id' => 'cosmos-footerContent-footerIcons', 'class' => 'cosmos-sidebarAligned']);

			// Open unordered list element for icon list
			$html .= Html::openElement('ul', ['id' => 'cosmos-footerIcons-list']);

			// Loop through each footer icon and generate a list item element
			// which contains the icon to display
			foreach ($footerIcons as $blockName => $footerIcons) {
				foreach ($footerIcons as $icon) {
					$html .= Html::openElement('li', ['id' => "cosmos-footerIcons-$blockName", 'class' => 'cosmos-footerIcons-listItem']);

					if (is_string($icon) || isset($icon['src'])) {
						$html .= $skin->makeFooterIcon($icon);
					}

					$html .= Html::closeElement('li');
				}
			}

			// Close unordered list element
			$html .= Html::closeElement('ul');

			// Close container div
			$html .= Html::closeElement('div');
		}
	}
	/**
	 * Builds HTML code to display the footer links, and appends it to the string
	 * that is passed to it.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildFooterLinks(string & $html, Config $config) {
		// Open container div for footer links
		$html .= Html::openElement('div', ['id' => 'cosmos-footerContent-footerLinks', 'class' => 'cosmos-articleAligned']);

		foreach ($this->getFooterLinks() as $category => $links) {
			// Open unordered list element for link list
			$html .= Html::openElement('ul', ['id' => "cosmos-footerLinks-$category", 'class' => 'cosmos-footerLinks-list']);

			foreach ($links as $key) {
				$html .= Html::rawElement('li', ['class' => 'cosmos-footerLinks-listItem'], $this->get($key));
			}
			// Close unordered list element
			$html .= Html::closeElement('ul');
		}

		// Close container div
		$html .= Html::closeElement('div');
	}
	/**
	 * Builds HTML code for the toolbar that is displayed at the bottom of the
	 * page, and appends it to the string of HTML that is it passed.
	 *
	 * @param $html string The string onto which the HTML should be appended
	 */
	protected function buildToolbar(string & $html, Config $config) {
		// Open container element for toolbar
		$html .= Html::openElement('section', ['id' => 'cosmos-toolbar']);

		// Open container div for toolbar content
		$html .= Html::openElement('div', ['id' => 'p-tb', 'class' => 'cosmos-toolbar-tools']);

		// Begin unordered list to contain tool links
		$html .= Html::openElement('div', ['class' => 'body cosmos-tools-list']);
		$html .= Html::openElement('ul');

		// Make a list item for each of the tool links
		$cosmosToolbar = new CosmosToolbar();
		if ($config->isEnabled('toolbar-message')) {
			$html .= $cosmosToolbar->getCode();
		} else {
			// to-do: Convert to Skin::buildNavUrls and Skin::buildFeedUrls
			foreach ($this->getToolbox() as $key => $toolbarItem) {
				// Due to some styles used in this skin, the printable version does not work
				// correctly at the moment, this will be fixed eventually, but for now just
				// remove it from the toolbar
				if ($key != 'print') {
					// to-do: convert to Skin::makeListItem
					$html .= $this->makeListItem($key, $toolbarItem);
				}
			}
			// Support CreateRedirect extension
			if (ExtensionRegistry::getInstance()->isLoaded('CreateRedirect')) {
				$skin = $this->getSkin();
				$action = $skin->getRequest()
					->getText('action', 'view');
				$title = $skin->getRelevantTitle();
				$href = SpecialPage::getTitleFor('CreateRedirect', $title->getPrefixedText())
					->getLocalURL();
				$CreateRedirect = Html::rawElement('li', ['id' => 't-createredirect'], Html::element('a', ['href' => $href], wfMessage('createredirect')->text()));
				if ($action == 'view' || $action == 'purge' || !$title->isSpecialPage()) {
					$html .= $CreateRedirect;
				}
			}
			if ($this->getMsg('cosmos-toolbar')
				->text() !== '-' && $this->getMsg('cosmos-toolbar')
				->text() !== '') {
				$html .= $cosmosToolbar->getCode();
			}
		}
		// Avoid PHP 7.1 warnings
		$skin = $this;
		Hooks::run('CosmosTemplateToolbarEnd', [&$skin, true]);

		// End unordered list
		$html .= Html::closeElement('ul');
		$html .= Html::closeElement('div');
		// Close container div
		$html .= Html::closeElement('div');

		// Close container element
		$html .= Html::closeElement('section');
	}

}
