<?php

namespace PPP\Wikidata\Wikipedia;

use Doctrine\Common\Cache\ArrayCache;
use PPP\Wikidata\Cache\PerSiteLinkCache;
use Wikibase\DataModel\SiteLink;

/**
 * @covers PPP\Wikidata\Wikipedia\MediawikiArticleImageProvider
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 * TODO: test continue
 */
class MediawikiArticleImageProviderTest extends \PHPUnit_Framework_TestCase {

	public function testGetImageForSiteLink() {
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
					'redirects' => true,
					'prop' => 'pageimages',
					'piprop' => 'thumbnail|name',
					'pithumbsize' => 300,
					'pilimit' => 50,
					'continue' => ''
				)))
			->will($this->returnValue(array(
				'query' => array(
					'pages' => array(
						array(
							'title' => 'Bar',
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

		$provider = new MediawikiArticleImageProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));

		$this->assertEquals(
			new MediawikiArticleImage(new SiteLink('enwiki', 'Bar'), 'http://test.org', 1, 1, 'foo'),
			$provider->getImageForSiteLink(new SiteLink('enwiki', 'Bar'))
		);
	}

	public function testGetImageForSiteLinkWithException() {
		$this->setExpectedException('\OutOfBoundsException');

		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();
		$mediawikiApiMock->expects($this->once())
			->method('getAction')
			->will($this->returnValue(array('query' => array('pages' => array()))));

		$provider = new MediawikiArticleImageProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));
		$provider->getImageForSiteLink(new SiteLink('enwiki', 'bar'));
	}

	public function testGetImageForSiteLinkWithCache() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();

		$cache = new PerSiteLinkCache(new ArrayCache(), 'mahp');
		$cache->save(new MediawikiArticleImage(new SiteLink('enwiki', 'Bar'), 'http://test.org', 1, 1, 'foo'));

		$provider = new MediawikiArticleImageProvider(array('enwiki' => $mediawikiApiMock), $cache);

		$this->assertEquals(
			new MediawikiArticleImage(new SiteLink('enwiki', 'Bar'), 'http://test.org', 1, 1, 'foo'),
			$provider->getImageForSiteLink(new SiteLink('enwiki', 'Bar'))
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
					'redirects' => true,
					'prop' => 'pageimages',
					'piprop' => 'thumbnail|name',
					'pithumbsize' => 300,
					'pilimit' => 50,
					'continue' => ''
				)))
			->will($this->returnValue(array(
				'query' => array(
					'pages' => array(
						array(
							'title' => 'Bar',
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

		$provider = new MediawikiArticleImageProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));

		$provider->loadFromSiteLinks(array(new SiteLink('enwiki', 'Bar'), new SiteLink('dewiki', 'Bar')));
		$this->assertEquals(
			new MediawikiArticleImage(new SiteLink('enwiki', 'Bar'), 'http://test.org', 1, 1, 'foo'),
			$provider->getImageForSiteLink(new SiteLink('enwiki', 'Bar'))
		);
	}

	public function testIsWikiIdSupported() {
		$mediawikiApiMock = $this->getMockBuilder('Mediawiki\Api\MediawikiApi')
			->disableOriginalConstructor()
			->getMock();

		$provider = new MediawikiArticleImageProvider(array('enwiki' => $mediawikiApiMock), new PerSiteLinkCache(new ArrayCache(), 'mahp'));

		$this->assertTrue($provider->isWikiIdSupported('enwiki'));
		$this->assertFalse($provider->isWikiIdSupported('dewiki'));
	}
}
