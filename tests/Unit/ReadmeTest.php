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
use OffsetWP\Twig\Extension\WordPressExtension;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * The README lists every function a template can call, and nothing a template cannot, and
 * every method of its variables.
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
		preg_match_all( '/`([a-zA-Z_][a-zA-Z0-9_]*)`/', $this->section( '## The functions' ), $matches );

		$this->assertEqualsCanonicalizing( array_keys( Catalogue::FUNCTIONS ), $matches[1] );
	}

	/**
	 * Every method of the variables is listed under the name a template reads it by, such as
	 * `site.option`: the README is where a theme learns that it exists.
	 *
	 * @return void
	 */
	public function testEveryMethodOfTheVariablesIsListed(): void {
		$section = $this->section( '## The variables' );

		foreach ( ( new WordPressExtension() )->getGlobals() as $name => $variable ) {
			foreach ( ( new \ReflectionClass( $variable ) )->getMethods( \ReflectionMethod::IS_PUBLIC ) as $method ) {
				if ( $method->isConstructor() ) {
					continue;
				}

				$this->assertStringContainsString(
					'`' . $name . '.' . $method->getName(),
					$section,
					sprintf( 'The README does not list %s.%s.', $name, $method->getName() )
				);
			}
		}
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
	 * Every example of Twig in the README compiles in an environment that has the extension.
	 * Twig refuses a function it does not know, and an argument a function does not take, so
	 * a name mistyped in an example fails here rather than in the theme of whoever copies it.
	 *
	 * @return void
	 */
	public function testEveryExampleOfTwigCompiles(): void {
		preg_match_all( '/```twig\n(.*?)```/s', $this->readme(), $blocks );

		$this->assertNotEmpty( $blocks[1], 'The README has no example of Twig.' );

		$examples = array();

		foreach ( $blocks[1] as $index => $block ) {
			$examples[ sprintf( 'README.md, example %d', $index + 1 ) ] = $block;
		}

		$twig = new Environment( new ArrayLoader( $examples ) );

		$twig->addExtension( new WordPressExtension() );

		foreach ( array_keys( $examples ) as $name ) {
			$this->assertSame( $name, $twig->load( $name )->getTemplateName() );
		}
	}

	/**
	 * One section of the README, from its heading to the next one, which has to exist.
	 *
	 * @param string $heading The heading of the section, as the README writes it.
	 * @return string
	 */
	private function section( string $heading ): string {
		$readme = $this->readme();
		$start  = strpos( $readme, $heading );
		$end    = false === $start ? false : strpos( $readme, "\n## ", $start + 1 );

		$this->assertNotFalse( $start, sprintf( 'The README has no "%s" section.', $heading ) );
		$this->assertNotFalse( $end, sprintf( 'The "%s" section of the README is the last one.', $heading ) );

		return substr( $readme, $start, $end - $start );
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
