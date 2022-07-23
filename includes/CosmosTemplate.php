<?php

namespace MediaWiki\Skins\Cosmos;

use BaseTemplate;
use Config;
use CookieWarning\Hooks as CookieWarningHooks;
use ExtensionRegistry;
use Html;
use Language;
use Linker;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\SpecialPage\SpecialPageFactory;
use Sanitizer;
use SiteStats;
use SpecialPage;
use Title;
use TitleFactory;
use UserProfilePage;
use WantedPagesPage;
use wAvatar;

class CosmosTemplate extends BaseTemplate {

	/** @var Config */
	protected $config;

	/** @var Language */
	private $contentLanguage;

	/** @var CosmosConfig */
	private $cosmosConfig;

	/** @var LanguageNameUtils */
	private $languageNameUtils;

	/** @var PermissionManager */
	private $permissionManager;

	/** @var SpecialPageFactory */
	private $specialPageFactory;

	/** @var TitleFactory */
	private $titleFactory;

	/** @var CosmosWordmarkLookup */
	private $wordmarkLookup;

	/**
	 * Outputs the entire contents of the page
	 *
	 * @return string
	 */
	public function execute() {
		/** @var SkinCosmos */
		$skin = $this->getSkin();
		'@phan-var SkinCosmos $skin';

		$this->config = $skin->config;
		$this->contentLanguage = $skin->contentLanguage;
		$this->cosmosConfig = $skin->cosmosConfig;
		$this->languageNameUtils = $skin->languageNameUtils;
		$this->permissionManager = $skin->permissionManager;
		$this->specialPageFactory = $skin->specialPageFactory;
		$this->titleFactory = $skin->titleFactory;
		$this->wordmarkLookup = $skin->wordmarkLookup;

		$html = $this->get( 'headelement' );
		$html .= $this->buildBanner();
		$html .= $this->buildCreateArticleDialog();
		$html .= Html::openElement( 'div', [ 'id' => 'mw-content-container', 'class' => 'ts-container' ] );
		$html .= Html::openElement( 'div', [ 'id' => 'mw-content-block', 'class' => 'ts-inner' ] );
		$html .= Html::openElement( 'div', [ 'id' => 'mw-content-wrapper' ] );
		$html .= $this->buildWikiHeader();
		$html .= $this->buildWiki();
		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'div' );
		$html .= $this->buildToolbar();
		$html .= Html::closeElement( 'div' );

		$html .= $this->getTrail();
		$html .= Html::closeElement( 'body' );
		$html .= Html::closeElement( 'html' );
		$title = $this->titleFactory->newFromText( $this->get( 'title' ) );
		if (
			class_exists( UserProfilePage::class ) &&
			(
				$this->config->get( 'CosmosSocialProfileShowGroupTags' ) ||
				$this->config->get( 'CosmosSocialProfileShowEditCount' ) ||
				$this->config->get( 'CosmosSocialProfileAllowBio' )
			) && (
				is_object( $title ) &&
				( $title->getNamespace() == NS_USER || $title->getNamespace() == NS_USER_PROFILE ) &&
				!$title->isSubpage()
			)
		) {

			// Set up Cosmos-specific SocialProfile elements
			$profileOwner = $title->getText();

			$replace = [
				'<div id="profile-title">' . $profileOwner . '</div>',
				'<div id="profile-title-container">'
			];

			$groupTags = $this->config->get( 'CosmosSocialProfileShowGroupTags' )
				? CosmosSocialProfile::getUserGroups( $profileOwner )
				: null;

			if ( $this->config->get( 'CosmosSocialProfileShowEditCount' ) ) {
				$contribsUrl = $this->titleFactory->newFromText(
					"Contributions/{$profileOwner}", NS_SPECIAL
				)->getFullURL();

				$editCount = Html::closeElement( 'br' );

				$editCount .= Html::rawElement( 'div', [
					'class' => [ 'contributions-details', 'tally' ]
				], Html::rawElement( 'a', [
					'href' => $contribsUrl
				], Html::rawElement( 'em', [],
					(string)CosmosSocialProfile::getUserEdits( $profileOwner )
				) .
				Html::rawElement( 'span', [],
					$this->getMsg( 'cosmos-editcount-label' )->escaped() .
					Html::closeElement( 'br' ) .
					CosmosSocialProfile::getUserRegistration( $profileOwner )
				) ) );
			} else {
				$editCount = null;
			}

			// experimental
			$followBioRedirects = $this->config->get( 'CosmosSocialProfileFollowBioRedirects' );

			$bio = $this->config->get( 'CosmosSocialProfileAllowBio' )
				? CosmosSocialProfile::getUserBio( $profileOwner, $followBioRedirects )
				: null;

			$replaceWith = [
				'<h1 itemprop="name">' . $profileOwner . '</h1>' . $groupTags . $editCount . $bio,
				'<div class="hgroup">'
			];

			return str_replace( $replace, $replaceWith, $html );
		} else {
			return $html;
		}
	}

	/**
	 * @return string
	 */
	protected function buildBanner() {
		$html = '';

		// Open container section for banner
		$html .= Html::openElement( 'section', [ 'id' => 'cosmos-banner' ] );
		// Open container div for banner content
		$html .= Html::openElement( 'div', [ 'id' => 'cosmos-banner-content', 'class' => 'cosmos-pageAligned' ] );

		// Build the mobile navigation
		$html .= Html::openElement( 'nav', [ 'class' => 'cosmos-mobile-navigation' ] );
		$html .= Html::rawElement(
			'div',
			[
				'class' => 'cosmos-button cosmos-button-primary cosmos-mobile-menu-button',
				'onclick' => '$(".wds-tabs").toggle()'
			],
			'&#9776;'
		);

		$html .= $this->buildNavigation();
		$html .= Html::closeElement( 'nav' );

		// Build the search bar
		$html .= $this->buildSearchBar();

		// Build user options/login button (floats on the right of the div)
		$html .= $this->buildUserOptions();

		// Close container div for banner content
		$html .= Html::closeElement( 'div' );

		// Close banner section
		$html .= Html::closeElement( 'section' );

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildCreateArticleDialog() {
		$skin = $this->getSkin();

		$html = '';
		$html .= Html::openElement( 'div', [ 'id' => 'createPageModal', 'class' => 'cosmos-modal' ] );
		$html .= Html::openElement( 'div', [ 'class' => 'cosmos-modal-content' ] );
		$html .= Html::rawElement( 'span', [ 'class' => 'close' ], '&times;' );
		$html .= Html::openElement(
			'form',
			[
				'class' => 'wds-dialog__wrapper create-page-dialog__wrapper',
				'action' => $this->get( 'wgScript' ),
				'method' => 'get'
			]
		);

		$html .= Html::hidden( 'action', 'edit' );

		$headerIcon = Icon::getIcon( 'close' )->makeSvg(
			14,
			14,
			[ 'class' => 'wds-icon wds-icon-small create-page-dialog__close' ]
		);

		$html .= Html::rawElement(
			'header',
			[ 'class' => 'wds-dialog__title' ],
			$this->getMsg( 'cosmos-createpage-header' )->escaped() . $headerIcon
		);

		$html .= Html::openElement( 'div', [ 'class' => 'wds-dialog__content' ] );
		$html .= Html::rawElement(
			'div',
			[ 'id' => 'create-page-dialog__message' ],
			$this->getMsg( 'cosmos-createpage-input-label' )->escaped()
		);

		$html .= Html::openElement( 'div', [ 'class' => 'wds-input create-page-dialog__title-wrapper' ] );

		$html .= Html::input(
			'title',
			'',
			'text',
			[ 'class' => 'wds-input__field', 'id' => 'create-page-dialog__title' ]
		);

		$html .= Html::closeElement( 'div' );

		$html .= Html::rawElement(
			'div',
			[ 'id' => 'create-page-dialog__message' ],
			$this->getMsg( 'cosmos-createpage-text',
				$skin->getLanguage()->formatNum( SiteStats::articles() ),
				$this->get( 'sitename' ),
				$this->config->get( 'CosmosEnableWantedPages' ) ?
					$this->getMsg( 'cosmos-createpage-wanted-pages' )->text() :
					$this->getMsg( 'cosmos-createpage-no-wanted-pages',
						SpecialPage::getTitleFor( 'Wantedpages' )
					)->text()
			)->parse()
		);

		$html .= Html::openElement( 'div', [ 'class' => 'create-page-dialog__proposals' ] );
		$html .= Html::openElement( 'ul', [ 'class' => 'articleProposals' ] );

		// Get most wanted pages
		if ( $this->config->get( 'CosmosEnableWantedPages' ) ) {
			foreach ( $this->getMostWantedPages() as $page ) {
				$html .= '<li><a href="' . $page['url'] . '" class="new">' . $page['title'] . '</a></li>';
			}
		}

		$html .= Html::closeElement( 'ul' );
		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'br' );
		$html .= Html::closeElement( 'br' );
		$html .= Html::closeElement( 'br' );
		$html .= Html::openElement( 'footer' );
		$html .= Html::openElement( 'div', [ 'class' => 'wds-dialog__actions' ] );

		$html .= Html::submitButton(
			$this->getMsg( 'cosmos-createpage-next' )->text(),
			[
				'class' => 'wds-button wds-is-text create-page-dialog__button',
				'disabled'
			]
		);

		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'footer' );
		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'form' );
		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * @return array
	 */
	protected function getMostWantedPages() {
		$wantedPagesPage = $this->specialPageFactory->getPage( 'Wantedpages' );
		'@phan-var WantedPagesPage $wantedPagesPage';

		$readFromCache = $this->config->get( 'CosmosFetchWantedPagesFromCache' );

		$wantedPagesPageResponse = $readFromCache ?
			$wantedPagesPage->fetchFromCache( false ) : $wantedPagesPage->doQuery();

		$wantedPages = [];

		$fetchedNamespaces = $this->config->get( 'CosmosWantedPagesFetchedNamespaces' );

		$fetchedTitlesCount = 0;
		$maxTitlesCount = $this->config->get( 'CosmosWantedPagesMaxTitlesCount' );

		foreach ( $wantedPagesPageResponse as $row ) {
			if (
				$row->title &&
				in_array( $row->namespace, $fetchedNamespaces ) &&
				$fetchedTitlesCount < $maxTitlesCount
			) {
				$wantedPageTitle = $this->titleFactory->newFromText(
					$row->title, $row->namespace
				);

				if (
					$wantedPageTitle instanceof Title &&
					!$wantedPageTitle->isKnown()
					&& !preg_match( '/[:\/]+/', $wantedPageTitle->getText() )
				) {
					$wantedPages[] = [
						'title' => $wantedPageTitle->getFullText(),
						'url' => $wantedPageTitle->getLocalURL( [
							'action' => 'edit',
							'source' => 'redlink',
						] ),
					];

					$fetchedTitlesCount++;
				}
			}
		}

		return $wantedPages;
	}

	/**
	 * @return string
	 */
	protected function buildNavigation() {
		$skin = $this->getSkin();

		$cosmosNavigation = new CosmosNavigation( $skin->getContext() );
		$html = '';
		$html .= Html::openElement( 'ul', [ 'class' => 'wds-tabs' ] );

		// Load site navigation links from MediaWiki:Cosmos-navigation
		$html .= $cosmosNavigation->getCode();

		// ManageWiki links
		if ( isset( $this->data['sidebar']['managewiki-sidebar-header'] ) ) {
			$dropdownIcon = Icon::getIcon( 'dropdown' )->makeSvg(
				14,
				14,
				[
					'id' => 'wds-icons-dropdown-tiny',
					'class' => 'wds-icon wds-icon-tiny wds-dropdown__toggle-chevron'
				]
			);

			$headerID = Sanitizer::escapeIdForAttribute( $this->getMsg( 'managewiki-sidebar-header' )->text() );
			$html .= Html::rawElement(
				'li',
				[ 'class' => 'wds-tabs__tab' ],
				'<div class="wds-dropdown" id="p-' . $headerID . '" aria-labelledby="p-' . $headerID . '-label">' .
				'<div class="wds-tabs__tab-label wds-dropdown__toggle" id="p-' . $headerID . '-label">' .
				'<span style="padding-top: 2px;">' . $this->getMsg( 'managewiki-sidebar-header' )->escaped() .
				'</span>' . $dropdownIcon . '</div><div class="wds-is-not-scrollable wds-dropdown__content">' .
				'<ul class="wds-list wds-is-linked wds-has-bolded-items">'
			);

			foreach ( $this->data['sidebar']['managewiki-sidebar-header'] as $module ) {
				$html .= '<li class="wds-tabs__tab"><a id="' . $module['id'] . '" href="' .
					htmlspecialchars( $module['href'] ) . '">' .
					$module['text'] . '</a></li>';
			}

			$html .= '</div>';
		}

		$html .= Html::closeElement( 'ul' );

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildUserOptions() {
		$html = '';

		// Open container div
		$html .= Html::openElement( 'div', [ 'id' => 'cosmos-banner-userOptions' ] );

		if ( $this->data['username'] ) {
			$html .= $this->buildNotifications();
		}

		$html .= $this->buildPersonalTools();

		// Close container div
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildPersonalTools() {
		$skin = $this->getSkin();

		$html = '';

		$html .= Html::openElement(
			'div',
			[
				'id' => 'p-personal',
				'class' => 'cosmos-userOptions-personalTools cosmos-dropdown cosmos-bannerOption',
				'aria-labelledby' => 'p-personal-label'
			]
		);

		$html .= Html::openElement(
			'div',
			[
				'id' => 'cosmos-personalTools-userButton',
				'class' => 'cosmos-dropdown-button cosmos-bannerOption-button'
			]
		);

		if ( class_exists( wAvatar::class ) && $this->config->get( 'CosmosUseSocialProfileAvatar' ) ) {
			$avatar = new wAvatar( $skin->getUser()
				->getId(), 'm' );
			$avatarElement = $avatar->getAvatarURL();
		} else {
			$avatarElement = Icon::getIcon( 'avatar' )->makeSvg( 28, 28 );
		}

		$html .= Html::rawElement(
			'div',
			[ 'id' => 'cosmos-userButton-avatar', 'class' => 'cosmos-bannerOption-icon' ],
			$avatarElement
		);

		$html .= Html::rawElement(
			'span',
			[ 'id' => 'p-personal-label', 'class' => 'cosmos-userButton-label' ],
			$this->get( 'username' ) ?? $this->getMsg( 'cosmos-anonymous' )->escaped()
		);

		$html .= Html::rawElement(
			'div',
			[ 'id' => 'cosmos-userButton-icon', 'class' => 'cosmos-dropdown-icon cosmos-bannerOption-dropdownIcon' ],
			Icon::getIcon( 'dropdown' )->makeSvg( 14, 14 )
		);

		$html .= Html::closeElement( 'div' );
		$html .= Html::openElement( 'div', [ 'class' => 'body cosmos-personalTools-list cosmos-dropdown-list' ] );
		$html .= Html::openElement( 'ul' );

		$personalTools = $this->get( 'personal_urls' );

		unset(
			$personalTools[ 'notifications-notice' ],
			$personalTools[ 'notifications-alert' ],
			$personalTools[ 'adminlinks' ]
		);

		foreach ( $personalTools as $key => $item ) {
			switch ( $key ) {
				case 'userpage':
					$item['text'] = $this->getMsg( 'cosmos-personaltools-userpage' )
						->escaped();
					break;
				case 'mytalk':
					$item['text'] = $this->getMsg( 'cosmos-personaltools-usertalk' )
						->escaped();
					break;
				case 'anontalk':
					$item['text'] = $this->getMsg( 'cosmos-personaltools-anontalk' )
						->escaped();
					break;
				default:
					break;
			}

			$tooltipMsg = $this->getMsg( "tooltip-pt-{$key}" );

			if ( !$tooltipMsg->isDisabled() ) {
				$item['title'] = $tooltipMsg->text();
			}

			$html .= Html::rawElement( 'li', [
				'id' => "pt-{$key}",
				'class' => [
					'active' => $item['active'] ?? false,
				],
			], Html::rawElement( 'a', [
				'class' => $item['link-class'] ?? $item['class'] ?? false,
				'href' => $item['href'] ?? false,
				'title' => $item['title'] ?? false,
			], $item['text'] ?? false ) );
		}

		$html .= Html::closeElement( 'ul' );
		$html .= Html::closeElement( 'div' );

		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildNotifications() {
		$skin = $this->getSkin();

		$html = '';

		if ( ExtensionRegistry::getInstance()->isLoaded( 'Echo' ) ) {
			$personalTools = $skin->getPersonalToolsForMakeListItem( $this->get( 'personal_urls' ) );

			$notificationIcons = [];
			$notificationIcons['notifications-alert'] = $personalTools['notifications-alert'];
			$notificationIcons['notifications-notice'] = $personalTools['notifications-notice'];

			$iconList = '';

			foreach ( $notificationIcons as $key => $item ) {
				$iconList .= $skin->makeListItem( $key, $item );
			}

			$html .= Html::rawElement(
				'div',
				[ 'id' => 'cosmos-notification-icons' ],
				Html::rawElement(
					'div',
					[ 'id' => 'cosmos-notifsButton-icon', 'class' => 'cosmos-bannerOption-icon' ],
					$iconList
				)
			);
		}

		return $html;
	}

	/**
	 * Builds HTML code to present the search form to the user, and appends it to
	 * string passed to it.
	 *
	 * @return string
	 */
	protected function buildSearchBar() {
		$skin = $this->getSkin();

		$html = '';

		// Open container div
		$html .= Html::openElement( 'div', [
			'id' => 'p-search',
			'class' => [
				'cosmos-banner-search',
				'cosmos-search-box',
			],
		] );

		// Open search form
		$html .= Html::openElement( 'form', [
			'action' => $this->get( 'wgScript' ),
			'id' => 'searchform',
			'class' => 'cosmos-search-box-form',
		] );

		$html .= Html::openElement( 'div', [
			'id' => 'simpleSearch',
		] );

		// Insert search bar
		$html .= $skin->makeSearchInput( [ 'id' => 'searchInput', 'class' => 'cosmos-search-input' ] );

		// Insert hidden search title
		$html .= Html::hidden( 'title', $this->get( 'searchtitle' ) );

		$html .= Html::openElement(
			'div',
			[ 'id' => 'cosmos-search-buttonContainer', 'class' => 'cosmos-bannerOption-button' ]
		);

		// Insert search icon
		$html .= Html::rawElement(
			'div',
			[ 'id' => 'cosmos-search-buttonIcon', 'class' => 'cosmos-bannerOption-icon' ],
			Icon::getIcon( 'search' )->makeSvg( 28, 28 )
		);

		// Insert search button
		$html .= $skin->makeSearchButton( 'go', [ 'id' => 'searchButton', 'class' => 'cosmos-search-button' ] );

		// Insert fallback search button
		$html .= $skin->makeSearchButton(
			'fulltext',
			[ 'id' => 'mw-searchButton', 'class' => 'mw-fallbackSearchButton cosmos-search-button' ]
		);

		$html .= Html::closeElement( 'div' );

		// Close form
		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'form' );

		// Close container div
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildWikiHeader() {
		$skin = $this->getSkin();
		$user = $skin->getUser();

		$canCreate = $this->permissionManager->userHasRight( $user, 'createpage' );
		$canEdit = $this->permissionManager->userHasRight( $user, 'edit' );
		$canRead = $this->permissionManager->userHasRight( $user, 'read' );
		$canUpload = $this->permissionManager->userHasRight( $user, 'upload' );
		$canAddVideo = $this->permissionManager->userHasRight( $user, 'addvideo' );
		$canViewAdminLinks = $this->permissionManager->userHasRight( $user, 'adminlinks' );

		$uploadsEnabled = $this->config->get( 'EnableUploads' );

		$specialUploadURL = $this->titleFactory->newFromText(
			'Upload', NS_SPECIAL
		)->getFullURL();

		$uploadNavigationURL = $this->config->get( 'UploadNavigationUrl' );

		$uploadURL = $uploadNavigationURL ?: $specialUploadURL;

		$videoExtensionLoaded = ExtensionRegistry::getInstance()->isLoaded( 'Video' );
		$adminLinksExtensionLoaded = ExtensionRegistry::getInstance()->isLoaded( 'Admin Links' );

		$recentChangesMessage = $this->getMsg( 'recentchanges' );
		$recentChangesURLMessage = $this->getMsg( 'recentchanges-url' );

		$cosmosAddNewPageTextMessage = $this->getMsg( 'cosmos-add-new-page-text' );

		$isAnon = !$this->get( 'username' );

		$html = '';

		$html .= Html::openElement(
			'header',
			[ 'class' => 'cosmos-header' ]
		);

		$html .= $this->buildWordmark();
		$html .= Html::openElement( 'div', [ 'class' => 'cosmos-header__top-container' ] );
		$html .= Html::openElement( 'div', [ 'class' => 'cosmos-header__sitename' ] );
		$html .= Html::rawElement(
			'a',
			[ 'href' => $this->data['nav_urls']['mainpage']['href'] ?? '#' ],
			$this->getMsg( 'cosmos-tagline' )->escaped()
		);

		$html .= Html::closeElement( 'div' );

		if ( $canRead ) {
			$html .= Html::openElement( 'div', [ 'class' => 'cosmos-header__counter' ] );
			$html .= Html::rawElement( 'span', [
					'class' => 'cosmos-header__counter-value'
				],
				$skin->getLanguage()->formatNum( SiteStats::articles() )
			);

			$html .= Html::rawElement(
				'span',
				[ 'class' => 'cosmos-header__counter-label' ],
				$this->getMsg( 'cosmos-counter-label' )->numParams( SiteStats::articles() )->escaped()
			);

			$html .= Html::closeElement( 'div' );
			$html .= Html::openElement( 'div', [ 'class' => 'cosmos-header__wiki-buttons wds-button-group' ] );

			if ( $canCreate && $canEdit ) {
				$newPageIcon = Icon::getIcon( 'newpage' )->makeSvg(
					1000,
					1000,
					[
						'class' => 'wds-icon wds-icon-small',
						'id' => 'wds-icons-page-small',
						'style' => ( !$isAnon && $canViewAdminLinks ? 'margin-right: 0;' : null )
					]
				);

				if ( $canViewAdminLinks ) {
					$newPageText = $isAnon ? $cosmosAddNewPageTextMessage->escaped() : null;
				} else {
					$newPageText = $isAnon
						? $this->getMsg( 'cosmos-anon-add-new-page-text' )->escaped()
						: $cosmosAddNewPageTextMessage->escaped();
				}

				$html .= Html::rawElement(
					'a',
					[
						'class' => 'wds-button wds-is-secondary createpage',
						'id' => 'createpage',
						'href' => '#create-article',
						'title' => $this->getMsg( 'cosmos-add-new-page-title' )->text()
					],
					$newPageIcon . $newPageText
				);
			}

			if ( !$isAnon || ( !$canEdit && !$canCreate ) ) {
				$recentChangesIcon = Icon::getIcon( 'recentchanges' )->makeSvg(
					22,
					22,
					[
						'class' => 'wds-icon-small',
						'id' => 'wds-icons-activity-small',
						'stroke' => 'currentcolor',
						'stroke-linecap' => 'round',
						'stroke-linejoin' => 'round',
						'stroke-width' => 2
					]
				);

				$recentChangesText = !$canEdit && !$canCreate
					? '&nbsp;&nbsp;' . $recentChangesMessage->escaped()
					: null;

				$html .= Html::rawElement(
					'a',
					[
						'class' => 'wds-button wds-is-secondary',
						'href' =>
							$this->titleFactory->newFromText(
								$recentChangesURLMessage->text(), NS_SPECIAL
							)->getFullURL(),
						'title' => ucwords( $recentChangesMessage->text() )
					],
					$recentChangesIcon . $recentChangesText
				);
			}

			if ( $canViewAdminLinks && $adminLinksExtensionLoaded ) {
				$html .= Html::rawElement(
					'a',
					[
						'class' => 'wds-button wds-is-secondary',
						'href' =>
							$this->titleFactory->newFromText(
								'AdminLinks', NS_SPECIAL
							)->getFullURL(),
						'title' => ucwords( $this->getMsg( 'adminlinks' )->text() )
					],
					Icon::getIcon( 'admindashboard' )->makeSvg(
						24,
						24,
						[ 'class' => 'wds-icon-small', 'id' => 'wds-icons-dashboard-small' ]
					)
				);
			}

			if (
				( !$isAnon && ( ( $canUpload && $uploadsEnabled ) ||
				( $canAddVideo && $videoExtensionLoaded ) ) ) ||
				( ( ( $canUpload && $uploadsEnabled ) ||
				( $canAddVideo && $videoExtensionLoaded ) ) && !$canEdit && !$canCreate )
			) {
				$moreIcon = Icon::getIcon( 'more' )->makeSvg(
					384,
					384,
					[ 'class' => 'wds-icon wds-icon-small', 'id' => 'wds-icons-more' ]
				);

				if ( $canUpload && $uploadsEnabled ) {
					$newImageHTML = '<li id="m-add-new-image"><a href="' .
						htmlspecialchars( $uploadURL ) .
						'">' . $this->getMsg( 'cosmos-add-new-image' )->escaped() . '</a></li>';
				} else {
					$newImageHTML = null;
				}

				if ( $canAddVideo ) {
					$newVideoHTML = '<li id="m-add-new-video"><a href="' .
						htmlspecialchars(
							$this->titleFactory->newFromText(
								'AddVideo', NS_SPECIAL
							)->getFullURL()
						) .
						'">' . $this->getMsg( 'cosmos-add-new-video' )->escaped() . '</a></li>';
				} else {
					$newVideoHTML = null;
				}

				$recentChangesURL = $this->titleFactory->newFromText(
					$recentChangesURLMessage->text(), NS_SPECIAL
				)->getFullURL();

				$html .= Html::rawElement(
					'div',
					[ 'class' => 'wds-dropdown', 'id' => 'p-more' ],
					'<div class="wds-button wds-is-secondary wds-dropdown__toggle">' . $moreIcon . '</div>' .
						'<div class="wds-dropdown__content wds-is-not-scrollable wds-is-right-aligned">' .
						'<ul class="wds-list wds-is-linked">' . $newImageHTML . $newVideoHTML .
						'<li id="m-recentchanges"><a href="' . htmlspecialchars( $recentChangesURL ) . '">' .
						$recentChangesMessage->escaped() . '</a></li></ul></div>'
				);
			}

			$html .= Html::closeElement( 'div' );
		}

		$html .= Html::closeElement( 'div' );
		$html .= Html::openElement(
			'nav',
			[ 'class' => 'cosmos-header__local-navigation', 'id' => 'p-cosmos-navigation' ]
		);

		$html .= $this->buildNavigation();
		$html .= Html::closeElement( 'nav' );
		$html .= Html::closeElement( 'header' );

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildWordmark() {
		$html = '';

		if ( $this->wordmarkLookup->getWordmarkUrl() ) {
			// Open container div for logo
			$html .= Html::openElement( 'div', [ 'class' => 'cosmos-header__wordmark' ] );

			// Open link element
			$html .= Html::openElement(
				'a',
				array_merge(
					[ 'href' => $this->data['nav_urls']['mainpage']['href'] ?? '#' ],
					Linker::tooltipAndAccesskeyAttribs( 'p-logo' )
				)
			);

			// Insert wordmark
			$logoSrc = $this->wordmarkLookup->getWordmarkUrl();

			$html .= Html::rawElement(
				'img',
				[ 'src' => $logoSrc, 'alt' => $this->get( 'sitename' ) ]
			);

			// Close link element
			$html .= Html::closeElement( 'a' );

			// Close container div
			$html .= Html::closeElement( 'div' );
		}

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildWiki() {
		$skin = $this->getSkin();

		$html = '';

		// Open container element for page body
		$html .= Html::openElement( 'section', [ 'id' => 'mw-content' ] );
		$html .= Html::openElement( 'div', [ 'id' => 'content', 'class' => [
				'cosmos-pageAligned', 'mw-body'
			]
		] );

		// Build the header
		$html .= $this->buildHeader();

		// Build the article content
		$html .= Html::openElement( 'div', [ 'class' => 'cosmos-articleContainer' ] );
		$html .= $this->buildArticle();

		// Build the rail
		$cosmosRail = new CosmosRail( $this->cosmosConfig, $skin->getContext() );
		$html .= $cosmosRail->buildRail();

		$html .= Html::closeElement( 'div' );

		// Close container element for page body
		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'section' );

		$html .= $this->buildFooter();

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildHeader() {
		$html = '';

		// Open container element for header
		$html .= Html::openElement( 'header', [ 'id' => 'cosmos-page-header' ] );

		// Build article header
		$html .= $this->buildArticleHeader();

		// Close container element
		$html .= Html::closeElement( 'header' );

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildArticle() {
		$html = '';

		// Open container element for article
		$html .= Html::openElement( 'article', [ 'id' => 'cosmos-pageBody-content' ] );

		// If it exists, insert the page subtitle
		if ( $this->data['subtitle'] ) {
			$html .= Html::rawElement( 'div', [ 'id' => 'cosmos-pageContent-subtitle' ], $this->get( 'subtitle' ) );
		}

		// If it exists, insert the article undelete message
		if ( $this->data['undelete'] ) {
			$html .= Html::rawElement( 'div', [ 'id' => 'cosmos-pageContent-undelete' ], $this->get( 'undelete' ) );
		}

		// If it exists, display the site notice at the top of the article
		// Check for dissmissable site notice extension
		$request = $this->getSkin()->getRequest();

		if ( ExtensionRegistry::getInstance()->isLoaded( 'DismissableSiteNotice' ) && $this->data['sitenotice'] ) {
			$html .= Html::rawElement( 'div', [ 'id' => 'siteNotice' ], $this->get( 'sitenotice' ) );
		} elseif (
			$this->data['sitenotice'] &&
			(
				!$request->getCookie( 'CosmosSiteNoticeState' ) ||
				$request->getCookie( 'CosmosSiteNoticeState' ) !== 'closed'
			)
		) {
			$html .= Html::openElement(
				'div',
				[
					'id' => 'cosmos-content-siteNotice',
					'data-site-notice-hash' => hash( 'crc32b', $this->get( 'sitenotice' ) )
				]
			);

			// Display the site notice close button
			$html .= Html::rawElement(
				'div',
				[ 'class' => 'cosmos-button cosmos-button-primary', 'id' => 'cosmos-siteNotice-closeButton' ],
				Icon::getIcon( 'close' )->makeSvg( 14, 14, [ 'id' => 'cosmos-siteNotice-closeIcon' ] )
			);

			$html .= Html::rawElement( 'div', [ 'id' => 'siteNotice' ], $this->get( 'sitenotice' ) );

			$html .= Html::closeElement( 'div' );
		}

		$html .= $this->get( 'bodytext' );

		// If appropriate, insert the category links at the bottom of the page
		if ( $this->data['catlinks'] ) {
			$html .= Html::rawElement( 'span', [ 'id' => 'cosmos-content-categories' ], $this->get( 'catlinks' ) );
		}

		// If there is any additional data or content to show, insert it now
		if ( $this->data['dataAfterContent'] ) {
			$html .= Html::rawElement(
				'span',
				[ 'id' => 'cosmos-content-additionalContent' ],
				$this->get( 'dataAfterContent' )
			);
		}

		// Close container element for article
		$html .= Html::closeElement( 'article' );

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildArticleHeader() {
		$html = '';

		$html .= $this->buildArticleCategories();
		$html .= $this->buildArticleInterlang();
		$html .= Html::openElement( 'div', [ 'id' => 'cosmos-header-articleHeader' ] );
		$html .= Html::openElement( 'h1', [ 'id' => 'cosmos-articleHeader-title', 'class' => 'firstHeading' ] );
		$html .= Html::rawElement( 'span', [ 'id' => 'cosmos-title-text' ], $this->get( 'title' ) );
		$html .= $this->getIndicators();
		$html .= Html::closeElement( 'h1' );
		$html .= Html::openElement( 'div', [ 'id' => 'cosmos-articleHeader-actions' ] );
		$html .= $this->buildActionButtons();
		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildArticleCategories() {
		$skin = $this->getSkin();
		$context = $skin->getContext();

		$categories = [];
		$categoryNames = $context->getOutput()->getCategories( 'normal' );

		foreach ( $categoryNames as $categoryName ) {
			$categoryTitle = $this->titleFactory->newFromText( $categoryName, NS_CATEGORY );

			if ( empty( $categoryTitle ) ) {
				continue;
			}

			array_push( $categories, $categoryTitle );
		}

		$hasMoreCategories = count( $categories ) > 4;

		$categoriesLinks = [];
		$categoriesArray = $hasMoreCategories ?
			array_slice( $categories, 0, 3 ) :
			$categories;

		foreach ( $categoriesArray as $i => $category ) {
			$categoriesLinks[] = Html::element( 'a', [
					'href' => $category->getLocalURL(),
					'id' => 'categories-top-' . $i,
				], $category->getText()
			);
		}

		$categoriesHTML = implode( ', ', $categoriesLinks );

		if ( $hasMoreCategories ) {
			$categoriesHTML .= $this->getMsg( 'cosmos-article-header-categories-more-separator' )->escaped();
		}

		$moreCategories = $hasMoreCategories ? array_slice( $categories, 3 ) : [];

		$inCategoriesText = $this->getMsg( 'cosmos-article-header-categories-in' )->escaped();
		$moreCategoriesText = $this->getMsg( 'cosmos-article-header-categories-more' )
			->numParams( count( $moreCategories ) )
			->escaped();

		if ( $context->canUseWikiPage() && $context->getWikiPage()->getTitle()->isMainPage() ) {
			$hasVisibleCategories = false;
		} else {
			$hasVisibleCategories = count( $categories ) > 0;
		}

		$html = '';
		if ( $hasVisibleCategories ) {
			$html .= Html::openElement( 'div', [
					'class' => 'page-header__categories',
				]
			);

			$html .= Html::rawElement( 'span', [
					'class' => 'page-header__categories-in',
				], $inCategoriesText
			);

			$html .= Html::openElement( 'div', [
					'class' => 'page-header__categories-links',
				]
			);

			$html .= $categoriesHTML;

			if ( $hasMoreCategories ) {
				$html .= Html::openElement( 'div', [
						'class' => [
							'wds-dropdown',
							'page-header__categories-dropdown',
						]
					]
				);

				$html .= Html::rawElement( 'a', [
						'class' => 'wds-dropdown__toggle',
					], $moreCategoriesText
				);

				$html .= Html::openElement( 'div', [
						'class' => [
							'wds-dropdown__content',
							'page-header__categories-dropdown-content',
							'wds-is-left-aligned',
						]
					]
				);

				$html .= Html::openElement( 'ul', [
						'class' => [
							'wds-list',
							'wds-is-linked',
						]
					]
				);

				foreach ( $moreCategories as $i => $category ) {
					$html .= Html::openElement( 'li' );

					$html .= Html::element( 'a', [
							'href' => $category->getLocalURL(),
							'id' => 'categories-top-more-' . $i,
						], $category->getText()
					);

					$html .= Html::closeElement( 'li' );
				}

				$html .= Html::closeElement( 'ul' );
				$html .= Html::closeElement( 'div' );
				$html .= Html::closeElement( 'div' );
			}

			$html .= Html::closeElement( 'div' );
			$html .= Html::closeElement( 'div' );
		}

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildArticleInterlang() {
		$skin = $this->getSkin();
		$html = '';

		if ( count( $this->data['content_navigation']['variants'] ?? [] ) != 0 || $this->get( 'language_urls' ) ) {
			$html .= Html::openElement( 'div', [ 'id' => 'cosmos-header-interlang' ] );

			$dropdownIcon = Icon::getIcon( 'dropdown' )->makeSvg( 14, 14, [
				'id' => 'wds-icons-dropdown-tiny',
				'class' => [
					'wds-icon',
					'wds-icon-tiny',
					'wds-dropdown__toggle-chevron',
				],
			] );

			// Language variant (varlang) links
			if ( count( $this->data['content_navigation']['variants'] ?? [] ) != 0 ) {

				// Special casing for variant to change label to selected.
				// Check the class of the item for a `selected` class and if so, propagate the items label to the main
				// label.
				$variantLabel = $this->getMsg( 'variants' )->text();
				foreach ( $this->data['content_navigation']['variants'] ?? [] as $item ) {
					if ( stripos( $item['class'] ?? '', 'selected' ) !== false ) {
						$variantLabel = $item['text'];
						break;
					}
				}

				$variantLinks = '';
				foreach ( $this->data['content_navigation']['variants'] ?? [] as $module ) {
					$variantLinks .= Html::rawElement(
						'li', [
							'class' => [
								$module['class'] ?? '',
								'ca-variants-' . $module['hreflang'],
								'mw-list-item',
								'wds-tabs__tab',
								'variant-link',
								'variant-' . $module['hreflang'],
							],
							'id' => $module['id']
						],
						Html::element(
							'a', [
								'href' => $module['href'],
								'lang' => $module['lang'],
								'hreflang' => $module['hreflang'],
								'id' => 'variant-' . $module['hreflang']
							], $module['text']
						)
					);
				}

				$html .= Html::rawElement(
					'div', [
						'class' => [
							'wds-dropdown',
							'page-header__variants',
							'mw-portlet',
							'mw-portlet-variants',
						],
						'id' => 'p-variants',
						'aria-labelledby' => 'p-variants-label'
					],
					Html::rawElement(
						'div', [
							'class' => [
								'wds-tabs__tab-label',
								'wds-dropdown__toggle',
							],
							'id' => 'p-lang-label',
						],
						Html::element(
							'span', [
								'class' => 'user-variant',
								'style' => 'padding-top: 2px; font-size: 14px;',
							], $variantLabel
						)
					) . $dropdownIcon . Html::rawElement(
						'div', [
							'class' => [
								'wds-dropdown__content',
								'wds-is-not-scrollable',
								'wds-is-right-aligned',
							],
						],
						Html::rawElement(
							'ul', [
								'class' => [
									'wds-list',
									'wds-is-linked',
								],
							], $variantLinks
						)
					)
				);
			}

			// Interlanguage (languages) links
			if ( $this->get( 'language_urls' ) ) {
				$title = $skin->getTitle();

				// Special casing for Language to change label to current page content language (not view language).
				$interlangLabel = $this->getMsg( 'otherlanguages' )->text();
				$pageLanguage = $title->getPageLanguage()->getCode();

				// Fix cases including special pages and Scribunto Lua module pages
				if ( $title->getNamespace() == NS_SPECIAL || !( $title->hasContentModel( CONTENT_MODEL_WIKITEXT ) ) ) {
					$pageLanguage = $this->contentLanguage->getCode();
				}

				$interlangLabel = $this->languageNameUtils->getLanguageName( $pageLanguage );

				$interlangLinks = '';
				foreach ( $this->get( 'language_urls', [] ) as $module ) {
					$interlangLinks .= Html::rawElement(
						'li', [
							'class' => [
								'wds-tabs__tab',
								'interlanguage-link',
								'interwiki-' . $module['hreflang'],
							],
						],
						Html::element(
							'a', [
								'href' => $module['href'],
								'lang' => $module['lang'],
								'hreflang' => $module['hreflang'],
								'title' => $module['title'],
								'id' => 'lang-' . $module['hreflang'],
							], $module['text']
						)
					);
				}

				$html .= Html::rawElement(
					'div', [
						'class' => [
							'wds-dropdown',
							'page-header__languages',
							'mw-portlet',
							'mw-portlet-lang',
						],
						'id' => 'p-lang',
						'aria-labelledby' => 'p-lang-label'
					],
					Html::rawElement(
						'div', [
							'class' => [
								'wds-tabs__tab-label',
								'wds-dropdown__toggle',
							],
							'id' => 'p-lang-label',
						],
						Html::element(
							'span', [
								'class' => 'user-language',
								'style' => 'padding-top: 2px; font-size: 14px;',
							], $interlangLabel
						)
					) . $dropdownIcon . Html::rawElement(
						'div', [
							'class' => [
								'wds-dropdown__content',
								'wds-is-right-aligned',
							],
						],
						Html::rawElement(
							'ul', [
								'class' => [
									'wds-list',
									'wds-is-linked',
								],
							], $interlangLinks
						)
					)
				);
			}

			$html .= Html::closeElement( 'div' );
		}

		return $html;
	}

	/**
	 * @return string
	 */
	protected function buildActionButtons() {
		$skin = $this->getSkin();
		$title = $skin->getRelevantTitle();

		$talkTitle = empty( $title ) ? null : $title->getTalkPageIfDefined();

		$isEditing = false;
		$isViewSource = false;
		$isHistory = false;
		$isSpecialAction = false;
		$isTalkPage = !empty( $title ) ? $title->isTalkPage() : false;

		$view = null;
		$edit = null;
		$talk = null;

		$dropdown = [];

		$html = '';

		// Sort through the flat content actions array provided by the API, and
		// extract, discard and modify what is necessary
		foreach ( $this->data['content_actions'] as $key => $tab ) {
			switch ( $key ) {
				// If the action is edit or view source, assign the tab array to the
				// edit variable, and specify the path to the image to use as the
				// button's icon
				case 'edit':
					$edit = $tab;
					$edit['imgType'] = 'svg';
					$edit['imgSrc'] = 'edit';
					if ( stripos( $tab['class'], 'selected' ) !== false ) {
						$isEditing = true;
					}
					break;
				case 'viewsource':
					$edit = $tab;
					$edit['imgType'] = 'svg';
					$edit['imgSrc'] = 'view';

					if ( stripos( $tab['class'], 'selected' ) !== false ) {
						$isViewSource = true;
					}
					break;

				// If the action is talk, assign the tab array to the talk variable and
				// specify the path to the button icon
				case 'talk':
					$talk = $tab;
					$talk['imgType'] = 'svg';
					$talk['imgSrc'] = 'talk';
					break;

				// If the action is add section, then replace the tooltip (which is, by
				// default, just a '+') with 'Add new section', a more appropriate
				// message for a drop-down list format and then DELIBERATELY fall
				// through to the default case
				case 'addsection':
					$tab['text'] = $this->getMsg( 'cosmos-action-addsection' )->text();

				// Finally, if the content action is none of the above, add it to the
				// growing array of miscellaneous content actions to be displayed in a
				// drop-down list beneath the edit/view soure button
				default:
					if ( substr( $key, 0, 6 ) === 'nstab-' ) {
						$view = $tab;
					} elseif ( substr( $key, 0, 8 ) !== 'varlang-' ) {
						if ( stripos( $tab['class'] ?? '', 'selected' ) === false ) {
							$dropdown[$key] = $tab;
						} else {
							if ( $key === 'history' ) {
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
		if ( !empty( $view ) ) {
			$view['class'] .= ' cosmos-actions-view';
		}

		if ( !empty( $edit ) ) {
			$edit['class'] .= ' cosmos-actions-edit';
		}

		if ( !empty( $talk ) ) {
			$talk['class'] .= ' cosmos-actions-talk';
		}

		$primary = '';
		$secondary = '';

		if ( $isEditing || $isSpecialAction ) {
			if ( $isTalkPage ) {
				// Primary button leads back to talk page
				if ( !empty( $talk ) ) {
					$talk['imgType'] = 'svg';
					$talk['imgSrc'] = 'cancel';
					$talk['text'] = $this->getMsg( 'cosmos-action-cancel' )->text();

					// Set href to the talk URL, so that if the talk page doesn't exist,
					// clicking the button while editing it doesn't use the redlink URL
					// that would take the user straight back to edit page
					if ( !empty( $talkTitle ) ) {
						$talk['href'] = $talkTitle->getLinkURL();
					}

					$primary = $talk;
				}

				// Secondary button leads back to article
				if ( !empty( $view ) ) {
					$view['imgType'] = 'svg';
					$view['imgSrc'] = 'back';
					$view['text'] = $this->getMsg( 'cosmos-action-backtopage', $view['text'] ?? false )->text();
					$secondary = $view;
				}
			} else {
				// Primary button leads back to article
				if ( !empty( $view ) ) {
					$view['imgType'] = 'svg';
					$view['imgSrc'] = 'cancel';
					$view['text'] = $this->getMsg( 'cosmos-action-cancel' )->text();

					// Set href to the page URL, so that if the page doesn't exist,
					// clicking the button while editing it doesn't use the redlink URL
					// that would take the user straight back to edit page
					if ( !empty( $title ) ) {
						$view['href'] = $title->getLinkURL();
					}
					$primary = $view;
				}

				// Secondary button leads to talk page
				if ( !empty( $talk ) ) {
					$talk['imgType'] = 'svg';
					$talk['imgSrc'] = 'talk';
					$secondary = $talk;
				}
			}

			// Edit pushed to dropdown
			if ( !$isEditing && !empty( $edit ) ) {
				array_unshift( $dropdown, $edit );
			}

		} elseif ( $isHistory || $isViewSource ) {
			if ( $isTalkPage ) {
				// Primary button leads back to talk page
				if ( !empty( $talk ) ) {
					$talk['imgType'] = 'svg';
					$talk['imgSrc'] = 'back';
					$talk['text'] = $this->getMsg( 'cosmos-action-back' )->text();
					$primary = $talk;
				}

				// Secondary button leads back to article
				if ( !empty( $view ) ) {
					$view['imgType'] = 'svg';
					$view['imgSrc'] = 'back';
					$view['text'] = $this->getMsg( 'cosmos-action-backtopage', $view['text'] ?? false )->text();
					$secondary = $view;
				}

			} else {
				// Primary button leads back to article
				if ( !empty( $view ) ) {
					$view['imgType'] = 'svg';
					$view['imgSrc'] = 'back';
					$view['text'] = $this->getMsg( 'cosmos-action-back' )->text();
					$primary = $view;
				}

				// Secondary button leads to talk page
				if ( !empty( $talk ) ) {
					$secondary = $talk;
				}
			}
			// Edit pushed to dropdown
			if ( !$isViewSource && !empty( $edit ) ) {
				array_unshift( $dropdown, $edit );
			}
		} else {
			if ( $isTalkPage ) {
				// Primary button leads to talk page edit
				if ( !empty( $edit ) ) {
					$primary = $edit;
				}

				// Secondary button leads back to article
				if ( !empty( $view ) ) {
					$view['imgType'] = 'svg';
					$view['imgSrc'] = 'back';
					$view['text'] = $skin->msg( 'cosmos-action-backtopage', $view['text'] ?? false )->text();
					$secondary = $view;
				}
			} else {
				// Primary button leads to article edit
				if ( !empty( $edit ) ) {
					$primary = $edit;
				}

				// Secondary button leads to talk page
				if ( !empty( $view ) ) {
					$secondary = $talk;
				}
			}
		}

		// Add Cosmos-specific classes to the primary and secondary buttons
		if ( !empty( $primary ) ) {
			$primary['class'] .= ' cosmos-button cosmos-button-primary cosmos-button-action';
		}

		if ( !empty( $secondary ) ) {
			$secondary['class'] .= ' cosmos-button cosmos-button-secondary cosmos-button-action';
		}

		// If the primary content action is available, display it as a button
		if ( !empty( $primary ) ) {
			$html .= $this->buildActionButton( $primary );
		}

		// If there are one or more miscellaneous content actions available,
		// display them as a drop-down list following the primary button
		if ( count( $dropdown ) > 0 ) {
			$html .= $this->buildActionDropdown( $dropdown );
		}

		// If the secondary content action is available, display it as a button
		if ( !empty( $secondary ) ) {
			$html .= $this->buildActionButton( $secondary );
		}

		return $html;
	}

	/**
	 * Builds HTML code to for an individual content action button, and appends
	 * it to the string passed
	 *
	 * @param array $info An array with the necessary info to build the button
	 * @return string
	 */
	protected function buildActionButton( array $info ) {
		$html = '';

		// If the button links to another page, surround it in an <a> element that
		// links there
		if ( !empty( $info['href'] ) ) {
			$html .= Html::openElement( 'a', [ 'href' => $info['href'], 'title' => $info['title'] ?? '' ] );
		}

		// Open a <div> for the button
		$html .= Html::openElement( 'div', [ 'id' => $info['id'], 'class' => $info['class'] ] );

		if ( isset( $info['imgSrc'] ) ) {
			// If the button is to have an icon, display the icon in the format
			// corresponding to the given image type
			switch ( $info['imgType'] ) {
				case 'svg':
					$icon = Icon::getIcon( $info['imgSrc'] );
					if ( !isset( $icon ) ) {
						break;
					}

					$html .= $icon->makeSvg( 28, 28, [ 'class' => 'cosmos-button-icon' ] );
					break;
				default:
					$stylePath = $this->getSkin()
						->getConfig()
						->get( 'StylePath' );

					$html .= Html::rawElement(
						'img',
						[ 'src' => $stylePath . '/Cosmos/resources/icons/' . $info['imgSrc'] ]
					);
					break;
			}
		}

		// Place the button text in a <span> element
		$html .= Html::element( 'span', [ 'class' => 'cosmos-button-text' ], $info['text'] );

		// Close the main button <div> element
		$html .= Html::closeElement( 'div' );

		// If necessary, close the <a> element surrounding the button too
		if ( isset( $info['href'] ) ) {
			$html .= Html::closeElement( 'a' );
		}

		return $html;
	}

	/**
	 * Builds HTML code to for a drop-down list of selectable content actions,
	 * and appends it to a given string
	 *
	 * @param array $items An array of items which should be placed in the list
	 * @return string
	 */
	protected function buildActionDropdown( array $items ) {
		$skin = $this->getSkin();

		$html = '';

		// Open a <div> element to contain the entire drop-down
		$html .= Html::openElement( 'div', [
			'class' => 'cosmos-dropdown',
			'id' => 'cosmos-actions-actionsList'
		] );

		// Open a div for a button that will display the list when hovered over
		// (this is achieved via CSS styling of the cosmos-dropdown,
		// cosmos-dropdown-button, cosmos-dropdown-icon and cosmos-dropdown-list classes)
		$html .= Html::openElement( 'div', [
			'class' => [
				'cosmos-button cosmos-button-primary',
				'cosmos-button-action',
				'cosmos-dropdown-button'
			],
			'id' => 'cosmos-actionsList-button'
		] );

		// Insert the dropdown icon
		$html .= Html::rawElement( 'div', [
			'id' => 'cosmos-actionsList-dropdownIcon',
			'class' => 'cosmos-dropdown-icon'
		], Icon::getIcon( 'dropdown' )->makeSvg( 14, 14 ) );

		// Close the button div
		$html .= Html::closeElement( 'div' );

		// Open an <ul> element to contain the list itself
		$html .= Html::openElement( 'ul', [ 'class' => 'cosmos-dropdown-list', 'id' => 'cosmos-actionsList-list' ] );

		// Step through the array and use the makeListItem to convert each of the
		// items into a properly formatted HTML <li> element
		foreach ( $items as $key => $value ) {
			$html .= $skin->makeListItem( $key, $value );
		}

		// Close the <ul> list container
		$html .= Html::closeElement( 'ul' );

		// Close the <div> container
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * Builds HTML code for the page foooter, and appends it to the string passed
	 * to it.
	 *
	 * @return string
	 */
	protected function buildFooter() {
		$html = '';

		// Open container element for footer
		$html .= Html::openElement( 'footer', [ 'id' => 'cosmos-footer' ] );

		// Open container element for footer content
		$html .= Html::openElement( 'div', [ 'class' => 'cosmos-pageAligned' ] );

		$html .= Html::openElement( 'div', [ 'id' => 'cosmos-footer-footerContent' ] );

		$html .= Html::openElement( 'div', [ 'class' => 'cosmos-articleContainer' ] );

		// Build the footer links
		$html .= $this->buildFooterLinks();

		// Build the footer icons
		$html .= $this->buildFooterIcons();

		$html .= Html::closeElement( 'div' );

		$html .= Html::closeElement( 'div' );

		// Close container element for footer content
		$html .= Html::closeElement( 'div' );

		// Close container element for footer
		$html .= Html::closeElement( 'footer' );

		return $html;
	}

	/**
	 * Builds HTML code to display the footer icons, and appends it to the string
	 * that is passed to it.
	 *
	 * @return string
	 */
	protected function buildFooterIcons() {
		$footerIcons = $this->get( 'footericons' );

		if ( count( $footerIcons ) <= 0 ) {
			return '';
		}

		$skin = $this->getSkin();

		$html = '';

		// Open container div for icons
		$html .= Html::openElement(
			'div',
			[ 'id' => 'cosmos-footerContent-footerIcons', 'class' => 'cosmos-sidebarAligned' ]
		);

		// Open unordered list element for icon list
		$html .= Html::openElement( 'ul', [ 'id' => 'cosmos-footerIcons-list' ] );

		// Loop through each footer icon and generate a list item element
		// which contains the icon to display
		foreach ( $footerIcons as $blockName => $footerIcons ) {
			foreach ( $footerIcons as $icon ) {
				$html .= Html::openElement(
					'li',
					[ 'id' => "cosmos-footerIcons-{$blockName}", 'class' => 'cosmos-footerIcons-listItem' ]
				);

				if ( is_string( $icon ) || isset( $icon['src'] ) ) {
					$html .= $skin->makeFooterIcon( $icon );
				}

				$html .= Html::closeElement( 'li' );
			}
		}

		// Close unordered list element
		$html .= Html::closeElement( 'ul' );

		// Close container div
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * Builds HTML code to display the footer links, and appends it to the string
	 * that is passed to it.
	 *
	 * @return string
	 */
	protected function buildFooterLinks() {
		$html = '';

		// Open container div for footer links
		$html .= Html::openElement(
			'div',
			[ 'id' => 'cosmos-footerContent-footerLinks', 'class' => 'cosmos-articleAligned' ]
		);

		foreach ( $this->getFooterLinks() as $category => $links ) {
			// Open unordered list element for link list
			$html .= Html::openElement(
				'ul',
				[ 'id' => "cosmos-footerLinks-{$category}", 'class' => 'cosmos-footerLinks-list' ]
			);

			foreach ( $links as $key ) {
				$html .= Html::rawElement( 'li', [ 'class' => 'cosmos-footerLinks-listItem' ], $this->get( $key ) );
			}

			// Close unordered list element
			$html .= Html::closeElement( 'ul' );
		}

		// Close container div
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * Builds HTML code for the toolbar that is displayed at the bottom of the
	 * page, and appends it to the string of HTML that is it passed.
	 *
	 * @return string
	 */
	protected function buildToolbar() {
		$skin = $this->getSkin();

		$html = '';

		// Open container element for toolbar
		$html .= Html::openElement( 'section', [ 'id' => 'cosmos-toolbar' ] );

		// Open container div for toolbar content
		$html .= Html::openElement( 'div', [ 'id' => 'p-tb', 'class' => 'cosmos-toolbar-tools' ] );

		// Begin unordered list to contain tool links
		$html .= Html::openElement( 'div', [ 'class' => 'body cosmos-tools-list' ] );
		$html .= Html::openElement( 'ul' );

		// Make a list item for each of the tool links
		foreach ( $this->data['sidebar']['TOOLBOX'] as $key => $toolbarItem ) {
			$html .= $skin->makeListItem( $key, $toolbarItem );
		}

		// Support CreateRedirect extension
		if ( ExtensionRegistry::getInstance()->isLoaded( 'CreateRedirect' ) ) {
			$action = $skin->getRequest()->getText( 'action', 'view' );
			$title = $skin->getRelevantTitle();

			$href = SpecialPage::getTitleFor( 'CreateRedirect', $title->getPrefixedText() )->getLocalURL();
			$createRedirect = Html::rawElement(
				'li',
				[ 'id' => 't-createredirect' ],
				Html::element( 'a', [ 'href' => $href ], $this->getMsg( 'createredirect' )->text() )
			);

			if ( $action == 'view' || $action == 'purge' || !$title->isSpecialPage() ) {
				$html .= $createRedirect;
			}
		}

		// End unordered list
		$html .= Html::closeElement( 'ul' );
		$html .= Html::closeElement( 'div' );

		// Close container div
		$html .= Html::closeElement( 'div' );

		// Close container element
		$html .= Html::closeElement( 'section' );

		if ( ExtensionRegistry::getInstance()->isLoaded( 'CookieWarning' ) ) {
			$cookieWarningHooks = new CookieWarningHooks();
			$html .= $cookieWarningHooks->onSkinAfterContent( $html, $skin );
		}

		return $html;
	}
}
