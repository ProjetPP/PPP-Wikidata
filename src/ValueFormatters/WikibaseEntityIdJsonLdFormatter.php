<?php

namespace PPP\Wikidata\ValueFormatters;

use InvalidArgumentException;
use OutOfBoundsException;
use PPP\Wikidata\Wikipedia\MediawikiArticleHeaderProvider;
use PPP\Wikidata\Wikipedia\MediawikiArticleImageProvider;
use stdClass;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;
use ValueFormatters\ValueFormatterBase;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\FingerprintProvider;
use Wikibase\DataModel\Term\Term;
use Wikibase\EntityStore\EntityStore;

/**
 * Returns the label of a given Wikibase entity id
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikibaseEntityIdJsonLdFormatter extends ValueFormatterBase {

	/**
	 * @var EntityStore
	 */
	private $entityStore;

	/**
	 * @var MediawikiArticleHeaderProvider
	 */
	private $articleHeaderProvider;

	/**
	 * @var MediawikiArticleImageProvider
	 */
	private $articleImageProvider;

	/**
	 * @param EntityStore $entityStore
	 * @param MediawikiArticleHeaderProvider $articleHeaderProvider
	 * @param MediawikiArticleImageProvider $articleImageProvider
	 * @param FormatterOptions $options
	 */
	public function __construct(
		EntityStore $entityStore,
		MediawikiArticleHeaderProvider $articleHeaderProvider,
		MediawikiArticleImageProvider $articleImageProvider,
		FormatterOptions $options
	) {
		$this->entityStore = $entityStore;
		$this->articleHeaderProvider = $articleHeaderProvider;
		$this->articleImageProvider = $articleImageProvider;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($entityId) {
		if(!($entityId instanceof EntityId)) {
			throw new InvalidArgumentException('$value should be an EntityId');
		}

		$entity = $this->entityStore->getEntityDocumentLookup()->getEntityDocumentForId($entityId);

		$resource = new stdClass();
		$resource->{'@type'} = 'Thing';
		$resource->{'@id'} = 'http://www.wikidata.org/entity/' . $entityId->getSerialization(); //TODO: option
		$resource->{'@reverse'} = new stdClass();
		$resource->potentialAction = array(
			$this->newViewAction(
				array(new Term('en', 'View on Wikidata'), new Term('fr', 'Voir sur Wikidata')),
				'//upload.wikimedia.org/wikipedia/commons/f/ff/Wikidata-logo.svg',
				'//www.wikidata.org/entity/' . $entityId->getSerialization()
			)
		);

		if($entity instanceof FingerprintProvider) {
			$this->addFingerprintToResource($entity->getFingerprint(), $resource);
		}

		if($entity instanceof Item) {
			$this->addArticleToResource($entity->getSiteLinkList(), $resource);
			$this->addImageToResource($entity->getSiteLinkList(), $resource);
		}

		return $resource;
	}

	private function addFingerprintToResource(Fingerprint $fingerprint, stdClass $resource) {
		$languageCode = $this->getOption(ValueFormatter::OPT_LANG);

		try {
			$resource->name = $this->newResourceFromTerm($fingerprint->getLabel($languageCode));
		} catch(OutOfBoundsException $e) {
		}

		try {
			$resource->description = $this->newResourceFromTerm($fingerprint->getDescription($languageCode));
		} catch(OutOfBoundsException $e) {
		}

		try {
			$aliasGroup = $fingerprint->getAliasGroup($languageCode);
			$resource->alternateName = array();
			foreach($aliasGroup->getAliases() as $alias) {
				$resource->alternateName[] = $this->newResourceFromTerm(new Term($aliasGroup->getLanguageCode(), $alias));
			}
		} catch(OutOfBoundsException $e) {
		}
	}

	private function newResourceFromTerm(Term $term) {
		$resource = new stdClass();
		$resource->{'@language'} = $term->getLanguageCode();
		$resource->{'@value'} = $term->getText();
		return $resource;
	}

	private function addArticleToResource(SiteLinkList $siteLinkList, stdClass $resource) {
		$wikiId = $this->getOption(ValueFormatter::OPT_LANG) . 'wiki';

		if(!$this->articleHeaderProvider->isWikiIdSupported($wikiId)) {
			return;
		}

		try {
			$header = $this->articleHeaderProvider->getHeaderForSiteLink($siteLinkList->getBySiteId($wikiId));

			$articleResource = new stdClass();
			$articleResource->{'@type'} = 'Article';
			$articleResource->{'@id'} = $header->getUrl();
			$articleResource->inLanguage = $header->getLanguageCode();
			$articleResource->headline = $header->getText();
			$articleResource->license = 'http://creativecommons.org/licenses/by-sa/3.0/';
			$articleResource->author = new stdClass();
			$articleResource->author->{'@type'} = 'Organization';
			$articleResource->author->{'@id'} = 'http://www.wikidata.org/entity/Q52';
			$articleResource->author->name = 'Wikipedia';

			$resource->{'@reverse'}->about = $articleResource;
			$resource->potentialAction[] = $this->newViewAction(
				array(new Term('en', 'View on Wikipedia'), new Term('fr', 'Voir sur Wikipédia')),
				'//upload.wikimedia.org/wikipedia/commons/thumb/8/80/Wikipedia-logo-v2.svg/64px-Wikipedia-logo-v2.svg.png',
				$header->getUrl()
			);
		} catch(OutOfBoundsException $e) {
		}
	}

	private function addImageToResource(SiteLinkList $siteLinkList, stdClass $resource) {
		$wikiId = $this->getOption(ValueFormatter::OPT_LANG) . 'wiki';

		if(!$this->articleImageProvider->isWikiIdSupported($wikiId)) {
			return;
		}

		try {
			$image = $this->articleImageProvider->getImageForSiteLink($siteLinkList->getBySiteId($wikiId));

			$resource->image = new stdClass();
			$resource->image->{'@type'} = 'ImageObject';
			$resource->image->{'@id'} = 'http://commons.wikimedia.org/wiki/Image:' . str_replace(' ', '_', $image->getTitle()); //TODO configure
			$resource->image->contentUrl = $image->getUrl();
			$resource->image->width = $image->getWidth();
			$resource->image->height = $image->getHeight();
			$resource->image->name = $image->getTitle();
		} catch(OutOfBoundsException $e) {
		}
	}

	private function newViewAction(array $nameTerms, $image, $target) {
		$actionResource = new stdClass();
		$actionResource->{'@type'} = 'ViewAction';
		$actionResource->name = array();
		foreach($nameTerms as $term) {
			$actionResource->name[] = $this->newResourceFromTerm($term);
		}
		$actionResource->image = $image;
		$actionResource->target = $target;
		return $actionResource;
	}
}
