<?php

namespace PPP\Wikidata;

use Doctrine\Common\Cache\ArrayCache;
use PPP\DataModel\FirstNode;
use PPP\DataModel\IntersectionNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\SentenceNode;
use PPP\DataModel\SortNode;
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

	private static $cache;

	public static function setUpBeforeClass() {
		self::$cache = new ArrayCache();
	}

	/**
	 * @dataProvider requestAndResponseProvider
	 */
	public function testBuildResponse(ModuleRequest $request, array $response) {
		$requestHandler = new WikidataRequestHandler(
			'https://www.wikidata.org/w/api.php',
			'https://wdq.wmflabs.org/api',
			self::$cache
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
					new SentenceNode(''),
					'a'
				),
				array()
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
					new ResourceListNode(array(new WikibaseEntityResourceNode(
						'Douglas Adams',
						new ItemId('Q42'),
						'English writer and humorist'
					))),
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
					'en',
					new IntersectionNode(array(
						new TripleNode(
							new MissingNode(),
							new ResourceListNode(array(new StringResourceNode('occupation'))),
							new ResourceListNode(array(new StringResourceNode('poet')))
						),
						new TripleNode(
							new MissingNode(),
							new ResourceListNode(array(new StringResourceNode('occupation'))),
							new ResourceListNode(array(new StringResourceNode('computer scientist')))
						)
					)),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(
						new WikibaseEntityResourceNode('Ada Lovelace', new ItemId('Q7259'), 'English mathematician, considered the first computer programmer'),
						new WikibaseEntityResourceNode('Subhash Kak', new ItemId('Q92830'), ''),
						new WikibaseEntityResourceNode('Piero Scaruffi', new ItemId('Q465428'), ''),
						new WikibaseEntityResourceNode('', new ItemId('Q5963597'), '')
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
						new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
						new TripleNode(
							new MissingNode(),
							new ResourceListNode(array(new StringResourceNode('instance of'))),
							new ResourceListNode(array(new StringResourceNode('human')))
						)
					)),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(new WikibaseEntityResourceNode(
						'Douglas Adams',
						new ItemId('Q42'),
						'English writer and humorist'
					))),
					array(
						'relevance' => 1
					)
				))
			),
			array(
				new ModuleRequest(
					'en',
					new FirstNode(new SortNode(
						new TripleNode(
							new ResourceListNode(array(new StringResourceNode('Douglas Adams'))),
							new ResourceListNode(array(new StringResourceNode('VIAF'))),
							new MissingNode()
						),
						new StringResourceNode('default')
					)),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new FirstNode(new SortNode(
						new ResourceListNode(array(new StringResourceNode('113230702'))),
						new StringResourceNode('default')
					))
				))
			),
			array(
				new ModuleRequest(
					'en',
					new SentenceNode('Douglas Adams'),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(new WikibaseEntityResourceNode(
						'Douglas Adams',
						new ItemId('Q42'),
						'English writer and humorist'
					))),
					array(
						'relevance' => 1
					)
				))
			),
			array(
				new ModuleRequest(
					'en',
					new SentenceNode('Who is Tpt?'),
					'a'
				),
				array()
			),
		);
	}
}
