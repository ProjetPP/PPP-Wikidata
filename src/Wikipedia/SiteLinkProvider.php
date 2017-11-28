<?php

namespace PPP\Wikidata\Wikipedia;

use Wikibase\DataModel\SiteLink;

/**
 * Interface for objects able to provide a SiteLink
 *
 * @licence AGPLv3+
 * @author Thomas Pellissier Tanon
 */
interface SiteLinkProvider {

	/**
	 * @return SiteLink
	 */
	public function getSiteLink();
}
