<?php

namespace MediaWiki\Skins\Cosmos;

use File;
use RepoGroup;
use TitleFactory;

class CosmosBackgroundLookup {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var RepoGroup */
	private $repoGroup;

	/** @var string */
	private $main;

	/** @var string */
	private $wikiHeader;

	/**
	 * @param TitleFactory $titleFactory
	 * @param RepoGroup $repoGroup
	 * @param string $main
	 * @param string $wikiHeader
	 */
	public function __construct(
		TitleFactory $titleFactory,
		RepoGroup $repoGroup,
		string $main,
		string $wikiHeader
	) {
		$this->titleFactory = $titleFactory;
		$this->repoGroup = $repoGroup;
		$this->main = $main;
		$this->wikiHeader = $wikiHeader;
	}

	/**
	 * @return string|null
	 */
	public function getMainBackgroundUrl(): ?string {
		if ( (bool)$this->main ) {
			if ( !$this->isBackgroundUrl( $this->main ) ) {
				$file = $this->getBackgroundFile( $this->main );

				if ( $file && $file->exists() ) {
					return $file->getUrl();
				}
			}

			return $this->main;
		}

		return null;
	}

	/**
	 * @return string|null
	 */
	public function getWikiHeaderBackgroundUrl(): ?string {
		if ( (bool)$this->wikiHeader ) {
			if ( !$this->isBackgroundUrl( $this->wikiHeader ) ) {
				$file = $this->getBackgroundFile( $this->wikiHeader );

				if ( $file && $file->exists() ) {
					return $file->getUrl();
				}
			}

			return $this->wikiHeader;
		}

		return null;
	}

	/**
	 * @param string $background
	 * @return bool
	 */
	public function isBackgroundUrl( string $background ) {
		return (bool)preg_match( '%^(?:(http|https|ftp):|)//(?:www\.)?.*$%i', $background );
	}

	/**
	 * @param string $background
	 * @return File|null
	 */
	public function getBackgroundFile( string $background ): ?File {
		$title = $this->titleFactory->makeTitle( NS_FILE, $background );

		return $this->repoGroup->findFile( $title ) ?: null;
	}
}
