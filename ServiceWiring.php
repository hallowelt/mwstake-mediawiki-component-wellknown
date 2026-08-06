<?php

use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\WellKnown\WellKnownFileRegistry;

return [
	'MWStake.WellKnown.FileRegistry' => static function ( MediaWikiServices $services ) {
		return new WellKnownFileRegistry();
	},
];
