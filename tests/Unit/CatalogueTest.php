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
use Twig\Extension\CoreExtension;
use Twig\Extension\DebugExtension;
use Twig\Extension\StringLoaderExtension;
use Twig\TwigFunction;

/**
 * The list of functions, checked against what it promises: every function the starter theme
 * calls, under a name Twig can parse and no function of Twig's own takes, declared by
 * WordPress itself.
 */
#[CoversNothing]
final class CatalogueTest extends TestCase {

	/**
	 * The functions the templates of the starter theme call, and the one its README shows.
	 *
	 * @var list<string>
	 */
	private const STARTER_THEME = array(
		'bloginfo',
		'body_class',
		'esc_attr__',
		'esc_html__',
		'esc_url',
		'get_search_form',
		'get_search_query',
		'get_the_archive_description',
		'get_the_archive_title',
		'get_the_author_meta',
		'get_the_date',
		'get_the_excerpt',
		'get_the_post_thumbnail',
		'get_the_title',
		'has_post_thumbnail',
		'home_url',
		'language_attributes',
		'the_content',
		'the_permalink',
		'the_posts_pagination',
		'wp_body_open',
		'wp_footer',
		'wp_head',
		'wp_nav_menu',
		'get_theme_file_uri',
	);

	/**
	 * The starter theme renders with this extension alone.
	 *
	 * @return void
	 */
	public function testTheStarterThemeFindsEveryFunctionItCalls(): void {
		$this->assertSame( array(), array_values( array_diff( self::STARTER_THEME, array_keys( Catalogue::FUNCTIONS ) ) ) );
	}

	/**
	 * A name Twig's lexer would not read as a name is a function no template could call.
	 *
	 * @return void
	 */
	public function testEveryNameIsOneATemplateCanCall(): void {
		foreach ( array_keys( Catalogue::FUNCTIONS ) as $name ) {
			$this->assertMatchesRegularExpression( '/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name );
		}
	}

	/**
	 * Twig lets the last declaration of a function win, without a word. A function of
	 * WordPress named like one of Twig's would replace it in every template, and fn() would
	 * replace whichever function of the catalogue were called fn.
	 *
	 * @return void
	 */
	public function testNoNameShadowsAFunctionOfTwigOrFn(): void {
		$twig = array();

		foreach ( array( new CoreExtension(), new DebugExtension(), new StringLoaderExtension() ) as $extension ) {
			foreach ( $extension->getFunctions() as $function ) {
				$twig[] = $function->getName();
			}
		}

		$this->assertNotEmpty( $twig );
		$this->assertSame( array(), array_values( array_intersect( array_keys( Catalogue::FUNCTIONS ), $twig ) ) );
		$this->assertArrayNotHasKey( WordPressExtension::FN, Catalogue::FUNCTIONS );
	}

	/**
	 * Every name is one WordPress declares, and every function takes its arguments by value.
	 *
	 * Asked of the stubs of WordPress, which declare every one of its functions with its
	 * signature. They are loaded in a process of their own: this one declares the functions
	 * the other tests call, and the two declarations would collide. The lowest dependencies
	 * install the stubs of the oldest WordPress this package supports, so the same assertion
	 * proves the floor and the newest release alike.
	 *
	 * A parameter taken by reference would be refused by Twig, which passes expressions.
	 *
	 * @return void
	 */
	public function testWordPressDeclaresEveryFunctionWithArgumentsByValue(): void {
		$stubs = dirname( __DIR__, 2 ) . '/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php';

		$this->assertFileExists( $stubs );

		// The stubs and the names reach the process as its arguments: the first, then the rest.
		$snippet = 'require $argv[1];
			$missing = array();
			$by_reference = array();
			foreach ( array_slice( $argv, 2 ) as $name ) {
				if ( ! function_exists( $name ) ) {
					$missing[] = $name;
					continue;
				}
				foreach ( ( new ReflectionFunction( $name ) )->getParameters() as $parameter ) {
					if ( $parameter->isPassedByReference() ) {
						$by_reference[] = $name;
					}
				}
			}
			echo json_encode( array( "missing" => $missing, "by_reference" => $by_reference ) );';

		// The stubs of an older WordPress declare signatures a newer PHP deprecates. Those
		// deprecations say nothing of the functions asked about, and are not reported; any
		// other diagnostic is, and fails the assertion with the output in its message.
		$command = sprintf(
			'%s -d memory_limit=1G -d error_reporting=%d -r %s -- %s 2>&1',
			escapeshellarg( PHP_BINARY ),
			E_ALL & ~E_DEPRECATED,
			escapeshellarg( $snippet ),
			implode( ' ', array_map( 'escapeshellarg', array( $stubs, ...array_keys( Catalogue::FUNCTIONS ) ) ) )
		);

		$lines  = array();
		$status = 0;

		exec( $command, $lines, $status );

		$output = implode( PHP_EOL, $lines );

		$this->assertSame( 0, $status, $output );
		$this->assertSame(
			array(
				'missing'      => array(),
				'by_reference' => array(),
			),
			json_decode( $output, true ),
			$output
		);
	}

	/**
	 * The extension hands Twig the whole catalogue, and fn() after it.
	 *
	 * @return void
	 */
	public function testTheExtensionDeclaresTheCatalogueAndFn(): void {
		$names = array_map(
			static fn ( TwigFunction $twig_function ): string => $twig_function->getName(),
			( new WordPressExtension() )->getFunctions()
		);

		$this->assertSame( array( ...array_keys( Catalogue::FUNCTIONS ), WordPressExtension::FN ), $names );
	}
}
