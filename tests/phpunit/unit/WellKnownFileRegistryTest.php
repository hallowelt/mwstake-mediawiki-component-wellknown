<?php

namespace MWStake\MediaWiki\Component\WellKnown\Tests\Unit;

use MWStake\MediaWiki\Component\WellKnown\WellKnownFileRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MWStake\MediaWiki\Component\WellKnown\WellKnownFileRegistry
 */
class WellKnownFileRegistryTest extends TestCase {

	public function testHasReturnsFalseForUnregisteredFile(): void {
		$registry = new WellKnownFileRegistry();

		$this->assertFalse( $registry->has( 'nonexistent.txt' ) );
	}

	public function testHasReturnsTrueForRegisteredFile(): void {
		$registry = new WellKnownFileRegistry();
		$registry->register( 'security.txt', static function () {
			return 'content';
		}, 'text/plain' );

		$this->assertTrue( $registry->has( 'security.txt' ) );
	}

	public function testGetReturnsNullForUnregisteredFile(): void {
		$registry = new WellKnownFileRegistry();

		$this->assertNull( $registry->get( 'nonexistent.txt' ) );
	}

	public function testGetReturnsEntryWithCallbackAndContentType(): void {
		$registry = new WellKnownFileRegistry();
		$callback = static function () {
			return '{"keys":[]}';
		};
		$registry->register( 'jwks.json', $callback, 'application/json' );

		$entry = $registry->get( 'jwks.json' );

		$this->assertNotNull( $entry );
		$this->assertSame( $callback, $entry['callback'] );
		$this->assertEquals( 'application/json', $entry['contentType'] );
	}

	public function testDefaultContentTypeIsApplicationJson(): void {
		$registry = new WellKnownFileRegistry();
		$registry->register( 'test.json', static function () {
			return '{}';
		} );

		$entry = $registry->get( 'test.json' );

		$this->assertEquals( 'application/json', $entry['contentType'] );
	}

	public function testCustomContentType(): void {
		$registry = new WellKnownFileRegistry();
		$registry->register( 'security.txt', static function () {
			return 'Contact: admin@example.com';
		}, 'text/plain' );

		$entry = $registry->get( 'security.txt' );

		$this->assertEquals( 'text/plain', $entry['contentType'] );
	}

	public function testCallbackIsInvocable(): void {
		$registry = new WellKnownFileRegistry();
		$registry->register( 'test.json', static function () {
			return '{"hello":"world"}';
		} );

		$entry = $registry->get( 'test.json' );
		$content = call_user_func( $entry['callback'] );

		$this->assertEquals( '{"hello":"world"}', $content );
	}

	public function testGetRegisteredFilesReturnsEmptyByDefault(): void {
		$registry = new WellKnownFileRegistry();

		$this->assertSame( [], $registry->getRegisteredFiles() );
	}

	public function testGetRegisteredFilesReturnsAllFilenames(): void {
		$registry = new WellKnownFileRegistry();
		$registry->register( 'jwks.json', static function () {
			return '{}';
		} );
		$registry->register( 'security.txt', static function () {
			return '';
		}, 'text/plain' );

		$files = $registry->getRegisteredFiles();

		$this->assertCount( 2, $files );
		$this->assertContains( 'jwks.json', $files );
		$this->assertContains( 'security.txt', $files );
	}

	public function testRegisterOverwritesPreviousEntry(): void {
		$registry = new WellKnownFileRegistry();
		$registry->register( 'test.json', static function () {
			return 'first';
		} );
		$registry->register( 'test.json', static function () {
			return 'second';
		} );

		$entry = $registry->get( 'test.json' );
		$content = call_user_func( $entry['callback'] );

		$this->assertEquals( 'second', $content );
	}
}
