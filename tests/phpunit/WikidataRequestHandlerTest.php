<?php

namespace PPP\Wikidata;


use Doctrine\Common\Cache\ArrayCache;
use PPP\DataModel\MissingNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TimeResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Module\DataModel\ModuleRequest;
use PPP\Module\DataModel\ModuleResponse;
use PPP\Wikidata\DataModel\WikibaseEntityResourceNode;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @covers PPP\Wikidata\WikidataRequestHandler
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikidataRequestHandlerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider requestAndResponseProvider
	 */
	public function testBuildResponse(ModuleRequest $request, array $response) {
		$requestHandler = new WikidataRequestHandler(
			'https://www.wikidata.org/w/api.php',
			'https://wdq.wmflabs.org/api',
			new ArrayCache()
		);
		$this->assertEquals($response, $requestHandler->buildResponse($request));
	}

	public function requestAndResponseProvider() {
		return array(
			array(
				new ModuleRequest(
					'en',
					new MissingNode(),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new MissingNode()
				))
			),
			array(
				new ModuleRequest(
					'en',
					new TimeResourceNode('1933-11'),
					'a',
					array(
						'accuracy' => 0.5
					)
				),
				array(new ModuleResponse(
					'en',
					new TimeResourceNode('1933-11'),
					array(
						'accuracy' => 0.25,
						'relevance' => 1
					)
				))
			),
			array(
				new ModuleRequest(
					'en',
					new TripleNode(
						new StringResourceNode('Douglas Adam'),
						new StringResourceNode('VIAF'),
						new MissingNode()
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new StringResourceNode('113230702'),
					array(
						'relevance' => 1
					)
				))
			),
			array(
				new ModuleRequest(
					'ru',
					new TripleNode(
						new MissingNode(),
						new StringResourceNode('VIAF'),
						new StringResourceNode('113230702')
					),
					'a'
				),
				array(new ModuleResponse(
					'ru',
					new WikibaseEntityResourceNode(
						'Дуглас Адамс',
						new ItemId('Q42'),
						'английский писатель, драматург и сценарист, автор серии книг «Автостопом по галактике».'
					),
					array(
						'relevance' => 1
					)
				))
			),
			array(
				new ModuleRequest(
					'en',
					new TripleNode(
						new TripleNode(
							new MissingNode(),
							new StringResourceNode('VIAF'),
							new StringResourceNode('113230702')
						),
						new StringResourceNode('Birth place'),
						new MissingNode()
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new WikibaseEntityResourceNode(
						'Cambridge',
						new ItemId('Q350'),
						'city and non-metropolitan district in England'
					),
					array(
						'relevance' => 1
					)
				))
			),
			array(
				new ModuleRequest(
					'en',
					new TripleNode(
						new MissingNode(),
						new StringResourceNode('son'),
						new TripleNode(
							new MissingNode(),
							new StringResourceNode('VIAF identifier'),
							new StringResourceNode('45777651')
						)
					),
					'a'
				),
				array(
					new ModuleResponse(
						'en',
						new WikibaseEntityResourceNode(
							'Setnakhte',
							new ItemId('Q312402'),
							'first pharaoh of the 20th dynasty'
						),
						array(
							'relevance' => 1
						)
					),
					new ModuleResponse(
						'en',
						new WikibaseEntityResourceNode('Tiy-Merenese', new ItemId('Q1321008')),
						array(
							'relevance' => 1
						)
					),
				)
			),
		);
	}
}
