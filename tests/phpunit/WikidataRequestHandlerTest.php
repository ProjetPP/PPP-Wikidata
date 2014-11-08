<?php

namespace PPP\Wikidata;

use DataValues\StringValue;
use DataValues\UnknownValue;
use Doctrine\Common\Cache\ArrayCache;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\Module\DataModel\ModuleRequest;
use PPP\Module\DataModel\ModuleResponse;
use Wikibase\DataModel\Entity\EntityIdValue;
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
					new StringResourceNode('Douglas Adam'),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new WikibaseResourceNode('Douglas Adam', new UnknownValue('Douglas Adam'))
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
					new WikibaseResourceNode('113230702', new StringValue('113230702'))
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
					new WikibaseResourceNode('Дуглас Адамс', new EntityIdValue(new ItemId('Q42')))
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
					new WikibaseResourceNode('Cambridge', new EntityIdValue(new ItemId('Q350')))
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
						new WikibaseResourceNode('Setnakhte', new EntityIdValue(new ItemId('Q312402')))
					),
					new ModuleResponse(
						'en',
						new WikibaseResourceNode('Tiy-Merenese', new EntityIdValue(new ItemId('Q1321008')))
					),
				)
			),
		);
	}
}
