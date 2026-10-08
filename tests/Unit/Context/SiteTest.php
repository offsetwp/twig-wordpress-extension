<?php
/**
 * OffsetWP Twig WordPress Extension Tests
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension\WordPressExtension\Tests\Unit\Context
 */

declare( strict_types=1 );

namespace OffsetWP\Twig\Extension\WordPressExtension\Tests\Unit\Context;

use OffsetWP\Twig\Extension\WordPressExtension\Context\Encoded;
use OffsetWP\Twig\Extension\WordPressExtension\Context\Site;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Twig\Markup;

/**
 * What each method of the site variable asks of WordPress, and how it hands the answer over.
 */
#[CoversClass( Site::class )]
#[CoversTrait( Encoded::class )]
final class SiteTest extends TestCase {

	/**
	 * The name of the site is handed over as WordPress keeps it, encoded, and marked safe so
	 * that Twig does not encode it a second time.
	 *
	 * @return void
	 */
	public function testTheNameIsMarkedSafeAsWordPressKeepsIt(): void {
		$name = ( new Site() )->name();

		$this->assertInstanceOf( Markup::class, $name );
		$this->assertSame( 'Fish &amp; Chips', (string) $name );
	}

	/**
	 * A site without a tagline has an empty string for one, which every condition reads as
	 * false, whatever the version of Twig.
	 *
	 * @return void
	 */
	public function testAMissingTaglineIsAnEmptyString(): void {
		$this->assertSame( '', ( new Site() )->description() );
	}

	/**
	 * The addresses, the locale, the language and the character set are what WordPress gives.
	 *
	 * @return void
	 */
	public function testEachMethodGivesWhatWordPressGives(): void {
		$site = new Site();

		$this->assertSame( 'https://example.test', $site->url() );
		$this->assertSame( 'https://example.test/menu', $site->url( '/menu' ) );
		$this->assertSame( 'en_GB', $site->locale() );
		$this->assertSame( 'en-GB', $site->language() );
		$this->assertSame( 'UTF-8', $site->charset() );
	}

	/**
	 * An option is handed over as WordPress stores it, not marked safe, and the default
	 * stands in for an option that does not exist.
	 *
	 * @return void
	 */
	public function testAnOptionIsHandedOverAsWordPressStoresIt(): void {
		$site = new Site();

		$this->assertSame( '<b>Fresh</b> & hot', $site->option( 'motto' ) );
		$this->assertFalse( $site->option( 'missing' ) );
		$this->assertSame( 'none', $site->option( 'missing', 'none' ) );
	}
}
