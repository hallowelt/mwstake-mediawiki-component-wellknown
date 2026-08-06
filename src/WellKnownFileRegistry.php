<?php

namespace MWStake\MediaWiki\Component\WellKnown;

/**
 * Registry for virtual files served under /.well-known/.
 *
 * Extensions register a filename and a callable that returns the file content.
 */
class WellKnownFileRegistry {

	/** @var array<string, array{callback: callable, contentType: string}> */
	private array $files = [];

	/**
	 * Register a virtual file.
	 *
	 * @param string $filename The filename (e.g. "jwks.json", "security.txt")
	 * @param callable $callback A callable that returns the file content as a string.
	 *   Signature: function(): string
	 * @param string $contentType MIME type of the response (default: application/json)
	 */
	public function register( string $filename, callable $callback, string $contentType = 'application/json' ): void {
		$this->files[$filename] = [
			'callback' => $callback,
			'contentType' => $contentType,
		];
	}

	/**
	 * Check if a file is registered.
	 *
	 * @param string $filename
	 * @return bool
	 */
	public function has( string $filename ): bool {
		return isset( $this->files[$filename] );
	}

	/**
	 * Get the registered entry for a file.
	 *
	 * @param string $filename
	 * @return array{callback: callable, contentType: string}|null
	 */
	public function get( string $filename ): ?array {
		return $this->files[$filename] ?? null;
	}

	/**
	 * Get all registered filenames.
	 *
	 * @return string[]
	 */
	public function getRegisteredFiles(): array {
		return array_keys( $this->files );
	}
}
