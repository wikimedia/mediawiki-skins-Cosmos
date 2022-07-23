<?php

namespace MediaWiki\Skins\Cosmos;

use File;
use RepoGroup;
use TitleFactory;

class CosmosWordmarkLookup {

	/** @var TitleFactory */
	private $titleFactory;

	/** @var RepoGroup */
	private $repoGroup;

	/** @var string */
	private $wordmark;

	/**
	 * @param TitleFactory $titleFactory
	 * @param RepoGroup $repoGroup
	 * @param string $wordmark
	 */
	public function __construct(
		TitleFactory $titleFactory,
		RepoGroup $repoGroup,
		string $wordmark
	) {
		$this->titleFactory = $titleFactory;
		$this->repoGroup = $repoGroup;
		$this->wordmark = $wordmark;
	}

	/**
	 * @return string|null
	 */
	public function getWordmarkUrl(): ?string {
		if ( (bool)$this->wordmark ) {
			if ( !$this->isWordmarkUrl() ) {
				$file = $this->getWordmarkFile();

				if ( $file && $file->exists() ) {
					return $file->getUrl();
				}
			}

			return $this->wordmark;
		}

		return null;
	}

	/**
	 * @return bool
	 */
	public function isWordmarkUrl() {
		return (bool)preg_match( '%^(?:(http|https|ftp):|)//(?:www\.)?.*$%i', $this->wordmark );
	}

	/**
	 * @return File|null
	 */
	public function getWordmarkFile(): ?File {
		$title = $this->titleFactory->makeTitle( NS_FILE, $this->wordmark );

		return $this->repoGroup->findFile( $title ) ?: null;
	}
}
