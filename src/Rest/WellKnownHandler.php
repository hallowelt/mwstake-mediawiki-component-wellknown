<?php

namespace MWStake\MediaWiki\Component\WellKnown\Rest;

use MediaWiki\Rest\Handler;
use MediaWiki\Rest\Response;
use MWStake\MediaWiki\Component\WellKnown\WellKnownFileRegistry;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * REST handler for GET /.well-known/{filename}
 *
 * Serves virtual files registered via the WellKnownFileRegistry.
 */
class WellKnownHandler extends Handler {

	private WellKnownFileRegistry $registry;

	/**
	 * @param WellKnownFileRegistry $registry
	 */
	public function __construct( WellKnownFileRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * @inheritDoc
	 */
	public function execute(): Response {
		$filename = $this->getValidatedParams()['filename'];

		if ( !$this->registry->has( $filename ) ) {
			return $this->getResponseFactory()->createHttpError(
				404,
				[ 'message' => "Unknown well-known file: $filename" ]
			);
		}

		$entry = $this->registry->get( $filename );
		$content = call_user_func( $entry['callback'] );

		$response = $this->getResponseFactory()->create();
		$response->setHeader( 'Content-Type', $entry['contentType'] );
		$response->setHeader( 'Cache-Control', 'public, max-age=3600' );
		$response->getBody()->write( $content );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function getParamSettings(): array {
		return [
			'filename' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}

	/** @inheritDoc */
	public function needsReadAccess(): bool {
		return false;
	}

	/** @inheritDoc */
	public function needsWriteAccess(): bool {
		return false;
	}
}
