<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_WELLKNOWN_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_WELLKNOWN_VERSION', '1.0.0' );

MWStake\MediaWiki\ComponentLoader\Bootstrapper::getInstance()
	->register( 'wellknown', static function () {
		$GLOBALS['wgServiceWiringFiles'][] = __DIR__ . '/ServiceWiring.php';

		$restFilePath = wfRelativePath( __DIR__ . '/rest-routes.json', $GLOBALS['IP'] );
		$GLOBALS['wgRestAPIAdditionalRouteFiles'][] = $restFilePath;
	} );
