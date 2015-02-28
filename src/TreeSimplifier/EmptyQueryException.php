<?php

namespace PPP\Wikidata\TreeSimplifier;

use RuntimeException;

/**
 * Exception used in order to avoid to do a query if the result can't be other than empty
 *
 * @licence GPLv2+
 * @author Thomas Pellissier Tanon
 */
class EmptyQueryException extends RuntimeException {
}
