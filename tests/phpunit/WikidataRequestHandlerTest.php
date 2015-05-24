<?php

namespace PPP\Wikidata;

use PPP\DataModel\FirstNode;
use PPP\DataModel\IntersectionNode;
use PPP\DataModel\JsonLdResourceNode;
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

/**
 * @covers PPP\Wikidata\WikidataRequestHandler
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class WikidataRequestHandlerTest extends \PHPUnit_Framework_TestCase {

	public static function getRequestHandler() {
		static $requestHandler = null;

		if($requestHandler === null) {
			$requestHandler = new WikidataRequestHandler(
				__DIR__ . '/../../default-config.json',
				array(
					'enwiki' => 'http://en.wikipedia.org/w/api.php',
					'dewiki' => 'http://de.wikipedia.org/w/api.php',
					'frwiki' => 'http://fr.wikipedia.org/w/api.php'
				)
			);
		}

		return $requestHandler;
	}

	/**
	 * @dataProvider requestAndResponseProvider
	 */
	public function testBuildResponse(ModuleRequest $request, array $response) {
		$computedResponse = $this->getRequestHandler()->buildResponse($request);
		if($this->cleverEquals($computedResponse, $response)) {
			$this->assertTrue(true);
		} else {
			$this->assertEquals($response, $computedResponse);
		}
	}

	private function cleverEquals($a, $b) {
		if(count($a) !== count($b)) {
			return false;
		}

		for($i = 0; $i < count($a); $i++) {
			if(!$a[$i]->equals($b[$i])) {
				return false;
			}
		}

		return true;
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
					array(),
					array("aaaa")
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
						new ResourceListNode(array(new StringResourceNode('Q42'))),
						new ResourceListNode(array(new StringResourceNode('P214'))),
						new MissingNode()
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(
						new JsonLdResourceNode(
							'foo',
							(object) array(
								'@context' => 'http://schema.org',
								'@type' => 'Text',
								'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
									'@value' => '113230702'
								)
							)
						)
					)),
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
					new ResourceListNode(array(new JsonLdResourceNode(
						'Douglas Adams',
						(object) array(
							'@context' => 'http://schema.org',
							'@id' => 'http://www.wikidata.org/entity/Q42',
							'name' => 'Douglas Adams'
						)
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
					new ResourceListNode(array(new JsonLdResourceNode(
						'Дуглас Адамс',
						(object) array(
							'@context' => 'http://schema.org',
							'@id' => 'http://www.wikidata.org/entity/Q42',
							'name' => 'Douglas Adams'
						)
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
					new ResourceListNode(array(new JsonLdResourceNode(
						'Cambridge',
						(object) array(
							'@context' => 'http://schema.org',
							'@id' => 'http://www.wikidata.org/entity/Q350',
							'name' => 'Cambridge'
						)
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
							new JsonLdResourceNode(
								'Setnakhte',
								(object) array(
									'@context' => 'http://schema.org',
									'@id' => 'http://www.wikidata.org/entity/Q312402',
									'name' => 'Setnakhte'
								)
							),
							new JsonLdResourceNode(
								'Tiy-Merenese',
								(object) array(
									'@context' => 'http://schema.org',
									'@id' => 'http://www.wikidata.org/entity/Q1321008',
									'name' => 'Tiy-Merenese'
								)
							)
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
						new JsonLdResourceNode(
							'foo',
							(object) array(
								'@context' => 'http://schema.org',
								'@type' => 'Text',
								'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
									'@value' => '113230702'
								)
							)
						),
						new JsonLdResourceNode(
							'foo',
							(object) array(
								'@context' => 'http://schema.org',
								'@type' => 'Text',
								'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
									'@value' => '34454460'
								)
							)
						)
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
						new JsonLdResourceNode(
							'foo',
							(object) array(
								'@context' => 'http://schema.org',
								'@type' => 'Text',
								'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
									'@value' => '113230702'
								)
							)
						),
						new JsonLdResourceNode(
							'foo',
							(object) array(
								'@context' => 'http://schema.org',
								'@type' => 'Text',
								'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
									'@value' => '34454460'
								)
							)
						)
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
					new ResourceListNode(array(new JsonLdResourceNode(
						'foo',
						(object) array(
							'@context' => 'http://schema.org',
							'@type' => 'Text',
							'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
								'@value' => '113230702'
							)
						)
					))),
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
						),
						new TripleNode(
							new MissingNode(),
							new ResourceListNode(array(new StringResourceNode('sex'))),
							new ResourceListNode(array(new StringResourceNode('female')))
						)
					)),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(
						new JsonLdResourceNode(
							'Ada Lovelace',
							(object) array(
								'@context' => 'http://schema.org',
								'@id' => 'http://www.wikidata.org/entity/Q7259',
								'name' => 'Ada Lovelace'
							)
						)
					)),
					array(
						'relevance' => 1
					)
				))
			),
			array(
				new ModuleRequest(
					'en',
					new TripleNode(
						new ResourceListNode(array(new StringResourceNode('Nicolas Sarkozy'))),
						new ResourceListNode(array(new StringResourceNode('daughter'))),
						new MissingNode()
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(new JsonLdResourceNode(
						'Giulia Sarkozy',
						(object) array(
							'@context' => 'http://schema.org',
							'@id' => 'http://www.wikidata.org/entity/Q16338096',
							'name' => 'Giulia Sarkozy'
						)
					))),
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
					new ResourceListNode(array(new JsonLdResourceNode(
						'Douglas Adams',
						(object) array(
							'@context' => 'http://schema.org',
							'@id' => 'http://www.wikidata.org/entity/Q42',
							'name' => 'Douglas Adams'
						)
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
						new ResourceListNode(array(new JsonLdResourceNode(
							'foo',
							(object) array(
								'@context' => 'http://schema.org',
								'@type' => 'Text',
								'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
									'@value' => '113230702'
								)
							)
						))),
						new StringResourceNode('default')
					))
				))
			),
			array(
				new ModuleRequest(
					'en',
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(array(new StringResourceNode('instance of'))),
						new ResourceListNode(array(new StringResourceNode('azertyyuiopqdf')))
					),
					'a'
				),
				array()
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
							new MissingNode(),
							new ResourceListNode(array(new StringResourceNode('VIAF of'))),
							new ResourceListNode(array(new StringResourceNode('Douglas Adams')))
						)
					)),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(new JsonLdResourceNode(
						'foo',
						(object) array(
							'@context' => 'http://schema.org',
							'@type' => 'Text',
							'http://www.w3.org/1999/02/22-rdf-syntax-ns#value' => (object) array(
								'@value' => '113230702'
							)
						)
					))),
					array(
						'relevance' => 1
					)
				))
			),
			/*array( TODO: Implement support of goecoordinates queries
				new ModuleRequest(
					'en',
					new TripleNode(
						new MissingNode(),
						new ResourceListNode(array(new StringResourceNode('location'))),
						new TripleNode(
							new ResourceListNode(array(new StringResourceNode('ENS Lyon'))),
							new ResourceListNode(array(new StringResourceNode('location'))),
							new MissingNode()
						)
					),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(
						new JsonLdResourceNode(
							'ENS Lyon',
							(object) array(
								'@context' => 'http://schema.org',
								'@id' => 'http://www.wikidata.org/entity/Q10159'
							)
						),
						new JsonLdResourceNode(
							'ENS Lyon',
							(object) array(
								'@context' => 'http://schema.org',
								'@id' => 'http://www.wikidata.org/entity/Q3214458'
							)
						)
					)),
					array(
						'relevance' => 1
					)
				))
			),*/
			array(
				new ModuleRequest(
					'en',
					new SentenceNode('Douglas Adams'),
					'a'
				),
				array(new ModuleResponse(
					'en',
					new ResourceListNode(array(new JsonLdResourceNode(
						'Douglas Adams',
						(object) array(
							'@context' => 'http://schema.org',
							'@id' => 'http://www.wikidata.org/entity/Q42',
							'name' => 'Douglas Adams'
						)
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
