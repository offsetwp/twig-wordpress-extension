<?php
/**
 * OffsetWP Twig WordPress Extension Tests
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension\WordPressExtension\Tests\Unit
 */

declare( strict_types=1 );

namespace OffsetWP\Twig\Extension\WordPressExtension\Tests\Unit;

use OffsetWP\Twig\Extension\WordPressExtension\Catalogue;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * The README lists every function a template can call, and nothing a template cannot.
 *
 * The list is the documentation a theme is written from, so a function added to the
 * catalogue and forgotten there is a function nobody knows of — and one listed there and
 * missing here is a template that fails. Both directions are asserted.
 */
#[CoversNothing]
final class ReadmeTest extends TestCase {

	/**
	 * Every function of the catalogue is listed, in backticks.
	 *
	 * @return void
	 */
	public function testEveryFunctionIsListed(): void {
		$readme = $this->readme();

		foreach ( array_keys( Catalogue::FUNCTIONS ) as $name ) {
			$this->assertStringContainsString( '`' . $name . '`', $readme, sprintf( 'The README does not list %s().', $name ) );
		}
	}

	/**
	 * And the list holds nothing else: as many names as the catalogue, under the heading of
	 * the list.
	 *
	 * @return void
	 */
	public function testTheListHoldsNothingElse(): void {
		$readme = $this->readme();
		$start  = strpos( $readme, '## The functions' );
		$end    = false === $start ? false : strpos( $readme, "\n## ", $start + 1 );

		$this->assertNotFalse( $start, 'The README has no "## The functions" section.' );
		$this->assertNotFalse( $end, 'The "## The functions" section of the README is the last one.' );

		preg_match_all( '/`([a-zA-Z_][a-zA-Z0-9_]*)`/', substr( $readme, $start, $end - $start ), $matches );

		$this->assertEqualsCanonicalizing( array_keys( Catalogue::FUNCTIONS ), $matches[1] );
	}

	/**
	 * The README says how many functions the list holds, and the number is the catalogue's:
	 * written by hand, it would stay behind the first function added.
	 *
	 * @return void
	 */
	public function testTheReadmeCountsTheFunctionsOfTheCatalogue(): void {
		$this->assertStringContainsString( count( Catalogue::FUNCTIONS ) . ' functions', $this->readme() );
	}

	/**
	 * The README, as it is published.
	 *
	 * @return string
	 */
	private function readme(): string {
		$readme = file_get_contents( dirname( __DIR__, 2 ) . '/README.md' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- A file of this repository, read by a test.

		$this->assertIsString( $readme );

		return $readme;
	}
}
