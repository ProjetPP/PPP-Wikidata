<?php

namespace PPP\Wikidata\TreeSimplifier;

use DataValues\TimeValue;
use DateInterval;
use DateTime;
use InvalidArgumentException;
use PPP\DataModel\AbstractNode;
use PPP\DataModel\IntersectionNode;
use PPP\DataModel\JsonLdResourceNode;
use PPP\DataModel\MissingNode;
use PPP\DataModel\ResourceListNode;
use PPP\DataModel\ResourceNode;
use PPP\DataModel\StringResourceNode;
use PPP\DataModel\TripleNode;
use PPP\DataModel\UnionNode;
use PPP\Module\TreeSimplifier\NodeSimplifier;
use PPP\Wikidata\ValueParsers\ResourceListNodeParser;
use PPP\Wikidata\WikibaseResourceNode;
use stdClass;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * Do some actions for specific use case:
 * - if a predicate is not useful like "name" or "identity" cast subjects to wikibase items
 * - if the predicte is son or daughter use "child" with an intersection with the relevant sex
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 *
 *
 * TODO: remove hardcoded values?
 */
class SpecificTripleNodeSimplifier implements NodeSimplifier {

	private static $MEANINGLESS_PREDICATES = array(
		'name',
		'identity',
		'definition'
	);

	const PROPERTY_CHILD = 'P40';
	const PROPERTY_SEX = 'P21';
	const PROPERTY_BIRTH_DATE = 'P569';
	const ITEM_MALE = 'Q6581097';
	const ITEM_FEMALE = 'Q6581072';

	/**
	 * @var ResourceListNodeParser
	 */
	private $resourceListNodeParser;

	/**
	 * @var ResourceListForEntityProperty
	 */
	private $resourceListForEntityProperty;

	/**
	 * @var DateTime
	 */
	private $now;

	/**
	 * @param ResourceListNodeParser $resourceListNodeParser
	 * @param ResourceListForEntityProperty $resourceListForEntityProperty
	 * @param DateTime $now
	 */
	public function __construct(ResourceListNodeParser $resourceListNodeParser, ResourceListForEntityProperty $resourceListForEntityProperty, DateTime $now) {
		$this->resourceListNodeParser = $resourceListNodeParser;
		$this->resourceListForEntityProperty = $resourceListForEntityProperty;
		$this->now = $now;
	}

	/**
	 * @see NodeSimplifier::isSimplifierFor
	 */
	public function isSimplifierFor(AbstractNode $node) {
		return $node instanceof TripleNode &&
			$node->getSubject() instanceof ResourceListNode &&
			$node->getPredicate() instanceof ResourceListNode &&
			$node->getObject() instanceof MissingNode;
	}

	/**
	 * @see NodeSimplifier::doSimplification
	 */
	public function simplify(AbstractNode $node) {
		if(!$this->isSimplifierFor($node)) {
			throw new InvalidArgumentException('SpecificTripleNodeSimplifier can only clean TripleNode objects');
		}

		return $this->doSimplification($node);
	}

	public function doSimplification(TripleNode $node) {
		$additionalNodes = array();
		$otherPredicates = array();

		/** @var ResourceNode $predicate */
		foreach($node->getPredicate() as $predicate) {
			if(in_array($predicate->getValue(), self::$MEANINGLESS_PREDICATES)) {
				$additionalNodes[] = $this->resourceListNodeParser->parse($node->getSubject(), 'wikibase-item');
			} else if($predicate->equals(new StringResourceNode('son'))) {
				$additionalNodes[] = $this->buildSonNode($node);
			} else if($predicate->equals(new StringResourceNode('daughter'))) {
				$additionalNodes[] = $this->buildDaughterNode($node);
			} else if($predicate->equals(new StringResourceNode('age'))) {
				$additionalNodes[] = $this->buildAgeNode($node);
			} else {
				$otherPredicates[] = $predicate;
			}
		}

		if(!empty($otherPredicates)) {
			$additionalNodes[] = new TripleNode($node->getSubject(), new ResourceListNode($otherPredicates), $node->getObject());
		}

		if(count($additionalNodes) === 1) {
			return $additionalNodes[0];
		}

		return new UnionNode($additionalNodes);
	}

	private function buildSonNode(TripleNode $node) {
		return new IntersectionNode(array(
			new TripleNode(
				$node->getSubject(),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId(self::PROPERTY_CHILD))))),
				$node->getObject()
			),
			new TripleNode(
				$node->getObject(),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId(self::PROPERTY_SEX))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId(self::ITEM_MALE)))))
			),
		));
	}

	private function buildDaughterNode(TripleNode $node) {
		return new IntersectionNode(array(
			new TripleNode(
				$node->getSubject(),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId(self::PROPERTY_CHILD))))),
				$node->getObject()
			),
			new TripleNode(
				$node->getObject(),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new PropertyId(self::PROPERTY_SEX))))),
				new ResourceListNode(array(new WikibaseResourceNode('', new EntityIdValue(new ItemId(self::ITEM_FEMALE)))))
			),
		));
	}

	private function buildAgeNode(TripleNode $node) {
		$subjectNodes = $this->resourceListNodeParser->parse($node->getSubject(), 'wikibase-item');
		$ageValues = array();

		/** @var WikibaseResourceNode $subjectNode */
		foreach($subjectNodes as $subjectNode) {
			$birthDateObjects = $this->resourceListForEntityProperty->getForEntityProperty(
				$subjectNode->getDataValue()->getEntityId(),
				new PropertyId(self::PROPERTY_BIRTH_DATE)
			);

			/** @var WikibaseResourceNode $birthDateObject */
			foreach($birthDateObjects as $birthDateObject) {
				$ageValues[] = $this->formatAgeValue($this->computeAge($birthDateObject->getDataValue()));
			}
		}

		return new ResourceListNode($ageValues);
	}

	private function computeAge(TimeValue $birthDateValue) {
		$birthDate = new DateTime(preg_replace('/^\+0*/', '', $birthDateValue->getTime()));
		return $this->now->diff($birthDate);
	}

	//TODO: implement a DurationValue?
	private function formatAgeValue(DateInterval $age) {
		$formattedAge = $age->format('%y');

		$literal = new stdClass();
		$literal->{'@type'} = 'Duration';
		$literal->{'@value'} = $age->format('P%yY%mM%dD');

		$resource = new stdClass();
		$resource->{'@context'} = 'http://schema.org';
		$resource->{'@type'} = 'Duration';
		$resource->{'name'} = $formattedAge;
		$resource->{'http://www.w3.org/1999/02/22-rdf-syntax-ns#value'} = $literal;

		return new JsonLdResourceNode($formattedAge, $resource);
	}
}
