<?php

namespace PPP\Wikidata;

use DataValues\StringValue;
use DataValues\UnknownValue;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceNode;
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
			'https://wdq.wmflabs.org/api'
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
					new MissingNode(),
					0.5
				))
			),
			array(
				new ModuleRequest(
					'en',
					new ResourceNode('Douglas Adam'),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new WikibaseResourceNode('Douglas Adam', new UnknownValue('Douglas Adam')),
					0.5
				))
			),
			array(
				new ModuleRequest(
					'en',
					new TripleNode(
						new ResourceNode('Douglas Adam'),
						new ResourceNode('VIAF'),
						new MissingNode()
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new WikibaseResourceNode('113230702', new StringValue('113230702')),
					0.5
				))
			),
			array(
				new ModuleRequest(
					'ru',
					new TripleNode(
						new MissingNode(),
						new ResourceNode('VIAF'),
						new ResourceNode('113230702')
					),
					'a'
				),
				array(new ModuleResponse(
					'ru',
					new WikibaseResourceNode('Дуглас Адамс', new EntityIdValue(new ItemId('Q42'))),
					0.5
				))
			),
			array(
				new ModuleRequest(
					'en',
					new TripleNode(
						new TripleNode(
							new MissingNode(),
							new ResourceNode('VIAF'),
							new ResourceNode('113230702')
						),
						new ResourceNode('Birth place'),
						new MissingNode()
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new WikibaseResourceNode('Cambridge', new EntityIdValue(new ItemId('Q350'))),
					0.5
				))
			),
			array(
				new ModuleRequest(
					'en',
					new TripleNode(
						new MissingNode(),
						new ResourceNode('son'),
						new TripleNode(
							new MissingNode(),
							new ResourceNode('VIAF identifier'),
							new ResourceNode('45777651')
						)
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new WikibaseResourceNode('Setnakhte', new EntityIdValue(new ItemId('Q312402'))),
					0.5
				))
			),
		);
	}
}
