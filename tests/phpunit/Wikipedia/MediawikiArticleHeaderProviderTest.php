<?php

namespace PPP\Wikidata\Wikipedia;

use Doctrine\Common\Cache\ArrayCache;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use Wikibase\DataModel\SiteLink;

/**
 * @covers PPP\Wikidata\Wikipedia\MediawikiArticleHeaderProvider
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class MediawikiArticleHeaderProviderTest extends \PHPUnit_Framework_TestCase {

	public function testGetHeaderForSiteLink() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();
		$mediawikiApiMock->expects($this->once())
			->method('getAction')
			->with(
				$this->equalTo('query'),
				$this->equalTo(array(
					'action' => 'query',
					'titles' => 'Bar',
					'prop' => 'extracts|info',
					'inprop' => 'url',
					'redirects' => true,
					'exintro' => true,
					'exsectionformat' => 'plain',
					'explaintext' => true,
					'exsentences' => 3,
					'exlimit' => 1
				)))
			->will($this->returnValue(array(
				'query' => array(
					'pages' => array(
						array(
							'title' => 'Bar',
							'extract' => 'foo',
							'pagelanguage' => 'en',
							'canonicalurl' => 'http://en.wikipedia.org/wiki/Bar'
						)
					)
				)
			)));

		$provider = new MediawikiArticleHeaderProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));

		$this->assertEquals(
			new MediawikiArticleHeader(new SiteLink('enwiki', 'Bar'), 'foo', 'en', 'http://en.wikipedia.org/wiki/Bar'),
			$provider->getHeaderForSiteLink(new SiteLink('enwiki', 'Bar'))
		);
	}

	public function testGetHeaderForSiteLinkWithException() {
		$this->setExpectedException('\OutOfBoundsException');

		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();
		$mediawikiApiMock->expects($this->once())
			->method('getAction')
			->will($this->returnValue(array('query' => array('pages' => array()))));

		$provider = new MediawikiArticleHeaderProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));
		$provider->getHeaderForSiteLink(new SiteLink('enwiki', 'bar'));
	}

	public function testGetHeaderForSiteLinkWithCache() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();

		$cache = new PerSiteLinkCache(new ArrayCache(), 'mahp');
		$cache->save(new MediawikiArticleHeader(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org'));

		$provider = new MediawikiArticleHeaderProvider(array('enwiki' => $mediawikiApiMock), $cache);

		$this->assertEquals(
			new MediawikiArticleHeader(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org'),
			$provider->getHeaderForSiteLink(new SiteLink('enwiki', 'bar'))
		);
	}

	public function testGetItemWithLoad() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();
		$mediawikiApiMock->expects($this->once())
			->method('getAction')
			->with(
				$this->equalTo('query'),
				$this->equalTo(array(
					'action' => 'query',
					'titles' => 'Bar',
					'prop' => 'extracts|info',
					'inprop' => 'url',
					'redirects' => true,
					'exintro' => true,
					'exsectionformat' => 'plain',
					'explaintext' => true,
					'exsentences' => 3,
					'exlimit' => 1
				)))
			->will($this->returnValue(array(
				'query' => array(
					'pages' => array(
						array(
							'title' => 'Bar',
							'extract' => 'foo',
							'pagelanguage' => 'en',
							'canonicalurl' => 'http://en.wikipedia.org/wiki/Bar'
						)
					)
				)
			)));

		$provider = new MediawikiArticleHeaderProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));

		$provider->loadFromSiteLinks(array(new SiteLink('enwiki', 'Bar')));
		$this->assertEquals(
			new MediawikiArticleHeader(new SiteLink('enwiki', 'Bar'), 'foo', 'en', 'http://en.wikipedia.org/wiki/Bar'),
			$provider->getHeaderForSiteLink(new SiteLink('enwiki', 'Bar'))
		);
	}

	public function testIsWikiIdSupported() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();

		$provider = new MediawikiArticleHeaderProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));

		$this->assertTrue($provider->isWikiIdSupported('enwiki'));
		$this->assertFalse($provider->isWikiIdSupported('dewiki'));
	}
}
