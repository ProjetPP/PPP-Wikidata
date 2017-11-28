<?php

namespace PPP\Wikidata\Wikipedia;

use Doctrine\Common\Cache\ArrayCache;
use Mediawiki\Api\SimpleRequest;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use Wikibase\DataModel\SiteLink;

/**
 * @covers PPP\Wikidata\Wikipedia\MediawikiArticleProvider
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 * TODO: test continue
 */
class MediawikiArticleProviderTest extends \PHPUnit_Framework_TestCase {

	public function testGetArticleForSiteLinkWithImage() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();
		$mediawikiApiMock->expects($this->once())
			->method('getRequest')
			->with($this->equalTo(new SimpleRequest(
				'query',
				array(
					'action' => 'query',
					'titles' => 'Bar',
					'prop' => 'extracts|info|pageimages',
					'inprop' => 'url',
					'redirects' => true,
					'exintro' => true,
					'exsectionformat' => 'plain',
					'explaintext' => true,
					'exsentences' => 3,
					'exlimit' => 20,
					'piprop' => 'thumbnail|name',
					'pithumbsize' => 300,
					'pilimit' => 20,
					'continue' => ''
				)
			)))
			->will($this->returnValue(array(
				'query' => array(
					'pages' => array(
						array(
							'title' => 'Bar',
							'extract' => 'foo',
							'pagelanguage' => 'en',
							'canonicalurl' => 'http://en.wikipedia.org/wiki/Bar',
							'thumbnail' => array(
								'source' => 'http://test.org',
								'width' => 1,
								'height' => 1
							),
							'pageimage' => 'foo'
						)
					)
				)
			)));

		$provider = new MediawikiArticleProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));

		$this->assertEquals(
			new MediawikiArticle(
				new SiteLink('enwiki', 'Bar'),
				'foo',
				'en',
				'http://en.wikipedia.org/wiki/Bar',
				new MediawikiArticleImage('http://test.org', 1, 1, 'foo')
			),
			$provider->getArticleForSiteLink(new SiteLink('enwiki', 'Bar'))
		);
	}

	public function testGetArticleForSiteLinkWithoutImage() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();
		$mediawikiApiMock->expects($this->once())
			->method('getRequest')
			->with($this->equalTo(new SimpleRequest(
				'query',
				array(
					'action' => 'query',
					'titles' => 'Bar',
					'prop' => 'extracts|info|pageimages',
					'inprop' => 'url',
					'redirects' => true,
					'exintro' => true,
					'exsectionformat' => 'plain',
					'explaintext' => true,
					'exsentences' => 3,
					'exlimit' => 20,
					'piprop' => 'thumbnail|name',
					'pithumbsize' => 300,
					'pilimit' => 20,
					'continue' => ''
				)
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

		$provider = new MediawikiArticleProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));

		$this->assertEquals(
			new MediawikiArticle(new SiteLink('enwiki', 'Bar'), 'foo', 'en', 'http://en.wikipedia.org/wiki/Bar'),
			$provider->getArticleForSiteLink(new SiteLink('enwiki', 'Bar'))
		);
	}

	public function testGetArticleForSiteLinkWithException() {
		$this->setExpectedException('\OutOfBoundsException');

		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();
		$mediawikiApiMock->expects($this->once())
			->method('getRequest')
			->will($this->returnValue(array('query' => array('pages' => array()))));

		$provider = new MediawikiArticleProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));
		$provider->getArticleForSiteLink(new SiteLink('enwiki', 'bar'));
	}

	public function testGetArticleForSiteLinkWithCache() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();

		$cache = new PerSiteLinkCache(new ArrayCache(), 'mahp');
		$cache->save(new MediawikiArticle(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org'));

		$provider = new MediawikiArticleProvider(array('enwiki' => $mediawikiApiMock), $cache);

		$this->assertEquals(
			new MediawikiArticle(new SiteLink('enwiki', 'bar'), 'foo', 'en', 'http://test.org'),
			$provider->getArticleForSiteLink(new SiteLink('enwiki', 'bar'))
		);
	}

	public function testGetArticleForSiteLinkWithLoad() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();
		$mediawikiApiMock->expects($this->once())
			->method('getRequest')
			->with($this->equalTo(new SimpleRequest(
				'query',
				array(
					'action' => 'query',
					'titles' => 'Bar',
					'prop' => 'extracts|info|pageimages',
					'inprop' => 'url',
					'redirects' => true,
					'exintro' => true,
					'exsectionformat' => 'plain',
					'explaintext' => true,
					'exsentences' => 3,
					'exlimit' => 20,
					'piprop' => 'thumbnail|name',
					'pithumbsize' => 300,
					'pilimit' => 20,
					'continue' => ''
				)
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

		$provider = new MediawikiArticleProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));

		$provider->loadFromSiteLinks(array(new SiteLink('enwiki', 'Bar'), new SiteLink('dewiki', 'Bar')));
		$this->assertEquals(
			new MediawikiArticle(new SiteLink('enwiki', 'Bar'), 'foo', 'en', 'http://en.wikipedia.org/wiki/Bar'),
			$provider->getArticleForSiteLink(new SiteLink('enwiki', 'Bar'))
		);
	}

	public function testIsWikiIdSupported() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();

		$provider = new MediawikiArticleProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));

		$this->assertTrue($provider->isWikiIdSupported('enwiki'));
		$this->assertFalse($provider->isWikiIdSupported('dewiki'));
	}
}
