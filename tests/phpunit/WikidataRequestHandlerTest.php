<?php

namespace PPP\Wikidata;

use Doctrine\Common\Cache\ArrayCache;
use PPP\DataModel\IntersectionNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\SentenceNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TimeResourceNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
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
					new ResourceListNode(array(new TimeResourceNode('1933-11'))),
					'a',
					array(
						'accuracy' => 0.5
					)
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(new TimeResourceNode('1933-11'))),
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
						new ResourceListNode(array(new StringResourceNode('Douglas Adam'))),
						new ResourceListNode(array(new StringResourceNode('VIAF'))),
						new MissingNode()
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(new StringResourceNode('113230702'))),
					array(
						'relevance' => 1
					)
				))
			),
			array(
				new ModuleRequest(
					'en',
					new TripleNode(
						new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
						new ResourceListNode(array(new StringResourceNode('name'))),
						new MissingNode()
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
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
						new ResourceListNode(array(new StringResourceNode('VIAF'))),
						new ResourceListNode(array(new StringResourceNode('113230702')))
					),
					'a'
				),
				array(new ModuleResponse(
					'ru',
					new ResourceListNode(array(new WikibaseEntityResourceNode(
						'Дуглас Адамс',
						new ItemId('Q42'),
						'английский писатель, драматург и сценарист, автор серии книг «Автостопом по галактике».'
					))),
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
							new ResourceListNode(array(new StringResourceNode('VIAF'))),
							new ResourceListNode(array(new StringResourceNode('113230702')))
						),
						new ResourceListNode(array(new StringResourceNode('Birth place'))),
						new MissingNode()
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(new WikibaseEntityResourceNode(
						'Cambridge',
						new ItemId('Q350'),
						'city and non-metropolitan district in England'
					))),
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
						new ResourceListNode(array(new StringResourceNode('son'))),
						new TripleNode(
							new MissingNode(),
							new ResourceListNode(array(new StringResourceNode('VIAF identifier'))),
							new ResourceListNode(array(new StringResourceNode('45777651')))
						)
					),
					'a'
				),
				array(
					new ModuleResponse(
						'en',
						new ResourceListNode(array(
							new WikibaseEntityResourceNode(
								'Setnakhte',
								new ItemId('Q312402'),
								'first pharaoh of the 20th dynasty'
							),
							new WikibaseEntityResourceNode('Tiy-Merenese', new ItemId('Q1321008'))
						)),
						array(
							'relevance' => 1
						)
					),
				)
			),
			array(
				new ModuleRequest(
					'en',
					new TripleNode(
						new ResourceListNode(array(
							new StringResourceNode('Douglas Adams'),
							new StringResourceNode('Jean-François Champollion')
						)),
						new ResourceListNode(array(new StringResourceNode('VIAF'))),
						new MissingNode()
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(
						new StringResourceNode('113230702'),
						new StringResourceNode('34454460')
					)),
					array(
						'relevance' => 1
					)
				))
			),
			array(
				new ModuleRequest(
					'en',
					new UnionNode(array(
						new TripleNode(
							new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
							new ResourceListNode(array(new StringResourceNode('VIAF'))),
							new MissingNode()
						),
						new TripleNode(
							new ResourceListNode(array(new StringResourceNode('Jean-François Champollion'))),
							new ResourceListNode(array(new StringResourceNode('VIAF'))),
							new MissingNode()
						)
					)),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(
						new StringResourceNode('113230702'),
						new StringResourceNode('34454460')
					)),
					array(
						'relevance' => 1
					)
				))
			),
			array(
				new ModuleRequest(
					'en',
					new IntersectionNode(array(
						new TripleNode(
							new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
							new ResourceListNode(array(new StringResourceNode('VIAF'))),
							new MissingNode()
						),
						new TripleNode(
							new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
							new ResourceListNode(array(new StringResourceNode('VIAF'))),
							new MissingNode()
						)
					)),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(new StringResourceNode('113230702'))),
					array(
						'relevance' => 1
					)
				))
			),
			array(
				new ModuleRequest(
					'fr',
					new TripleNode(
						new ResourceListNode(array(new StringResourceNode('Léon de la Brière'))),
						new ResourceListNode(array(new StringResourceNode('Identity'))),
						new MissingNode()
					),
					'a'
				),
				array(new ModuleResponse(
					'fr',
					new ResourceListNode(array(new StringResourceNode('Léon Leroy de la Brière (14 janvier 1845 - 12 septembre 1899) est un écrivain politique français de la fin du XIXe siècle.'))),
					array(
						'relevance' => 1
					)
				)),
			),
			array(
				new ModuleRequest(
					'fr',
					new SentenceNode('Léon de la Brière'),
					'a'
				),
				array(new ModuleResponse(
					'fr',
					new ResourceListNode(array(new StringResourceNode('Léon Leroy de la Brière (14 janvier 1845 - 12 septembre 1899) est un écrivain politique français de la fin du XIXe siècle.'))),
					array(
						'relevance' => 1
					)
				)),
			),
			array(
				new ModuleRequest(
					'en',
					new SentenceNode('Who is Obama'),
					'a'
				),
				array(),
			),
		);
	}
}
