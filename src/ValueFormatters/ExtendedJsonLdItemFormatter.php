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
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\DataModel\Term\Term;

/**
 * Returns the label of a given Wikibase entity id
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class ExtendedJsonLdItemFormatter extends ValueFormatterBase {

	/**
	 * @var ValueFormatter
	 */
	private $itemFormatter;

	/**
	 * @var MediawikiArticleHeaderProvider
	 */
	private $articleHeaderProvider;

	/**
	 * @var MediawikiArticleImageProvider
	 */
	private $articleImageProvider;

	/**
	 * @param ValueFormatter $itemFormatter
	 * @param MediawikiArticleHeaderProvider $articleHeaderProvider
	 * @param MediawikiArticleImageProvider $articleImageProvider
	 * @param FormatterOptions $options
	 */
	public function __construct(
		ValueFormatter $itemFormatter,
		MediawikiArticleHeaderProvider $articleHeaderProvider,
		MediawikiArticleImageProvider $articleImageProvider,
		FormatterOptions $options
	) {
		$this->itemFormatter = $itemFormatter;
		$this->articleHeaderProvider = $articleHeaderProvider;
		$this->articleImageProvider = $articleImageProvider;

		parent::__construct($options);
	}

	/**
	 * @see ValueFormatter::format
	 */
	public function format($value) {
		if(!($value instanceof Item)) {
			throw new InvalidArgumentException('$value is not an Item');
		}

		return $this->toJsonLd($value);
	}

	private function toJsonLd(Item $item) {
		$resource = $this->itemFormatter->format($item);

		$resource->potentialAction = array(
			$this->newViewAction(
				array(new Term('en', 'View on Wikidata'), new Term('fr', 'Voir sur Wikidata')),
				'//upload.wikimedia.org/wikipedia/commons/f/ff/Wikidata-logo.svg',
				'//www.wikidata.org/entity/' . $item->getId()->getSerialization()
			)
		);

		$this->addArticleToResource($item->getSiteLinkList(), $resource);
		$this->addImageToResource($item->getSiteLinkList(), $resource);

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

	private function newResourceFromTerm(Term $term) {
		$resource = new stdClass();
		$resource->{'@language'} = $term->getLanguageCode();
		$resource->{'@value'} = $term->getText();
		return $resource;
	}
}
