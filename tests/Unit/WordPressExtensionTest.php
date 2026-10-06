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
use OffsetWP\Twig\Extension\WordPressExtension\Result;
use OffsetWP\Twig\Extension\WordPressExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Twig\Node\EmptyNode;
use Twig\TwigFunction;

/**
 * How each function reaches Twig, and what fn() gives back when PHP calls it directly.
 */
#[CoversClass( WordPressExtension::class )]
final class WordPressExtensionTest extends TestCase {

	/**
	 * A function that returns is handed to Twig as WordPress declares it: its own name as
	 * the callable, which Twig compiles to a plain call.
	 *
	 * @return void
	 */
	public function testAFunctionThatReturnsIsHandedToTwigAsItIs(): void {
		$function = $this->declared( 'get_the_title' );

		$this->assertSame( Result::Returned, Catalogue::FUNCTIONS['get_the_title'] );
		$this->assertSame( 'get_the_title', $function->getCallable() );
		$this->assertFalse( $function->isVariadic() );
		$this->assertSame( array( 'html' ), $function->getSafe( new EmptyNode() ) );
	}

	/**
	 * A function that prints is handed to Twig wrapped, variadic so that its named arguments
	 * reach WordPress.
	 *
	 * @return void
	 */
	public function testAFunctionThatPrintsIsHandedToTwigWrapped(): void {
		$function = $this->declared( 'the_permalink' );

		$this->assertSame( Result::Printed, Catalogue::FUNCTIONS['the_permalink'] );
		$this->assertInstanceOf( \Closure::class, $function->getCallable() );
		$this->assertTrue( $function->isVariadic() );
		$this->assertSame( array( 'html' ), $function->getSafe( new EmptyNode() ) );
	}

	/**
	 * A function that returns, and that WordPress has not declared when Twig builds its list,
	 * is wrapped as one that prints is, so that it is looked up again when a template calls
	 * it.
	 *
	 * @return void
	 */
	public function testAFunctionThatReturnsAndIsNotDeclaredYetIsWrapped(): void {
		$function = $this->declared( 'get_bloginfo' );

		$this->assertFalse( function_exists( 'get_bloginfo' ) );
		$this->assertSame( Result::Returned, Catalogue::FUNCTIONS['get_bloginfo'] );
		$this->assertInstanceOf( \Closure::class, $function->getCallable() );
		$this->assertTrue( $function->isVariadic() );
	}

	/**
	 * Every function is safe for HTML, fn() included, whatever it gives back.
	 *
	 * @return void
	 */
	public function testEveryFunctionIsSafeForHtml(): void {
		foreach ( ( new WordPressExtension() )->getFunctions() as $function ) {
			$this->assertSame( array( 'html' ), $function->getSafe( new EmptyNode() ), $function->getName() );
		}
	}

	/**
	 * A function of a plugin, called through fn(), gives back what it returns.
	 *
	 * @return void
	 */
	public function testFnGivesBackWhatAFunctionReturns(): void {
		$this->assertSame( 'Hello, Ada!', WordPressExtension::call( 'offsetwp_test_greet', 'Ada' ) );
	}

	/**
	 * A named argument reaches the function under its name.
	 *
	 * @return void
	 */
	public function testFnPassesNamedArgumentsOn(): void {
		$this->assertSame( 'Hi, Ada!', WordPressExtension::call( 'offsetwp_test_greet', 'Ada', greeting: 'Hi' ) );
	}

	/**
	 * A function of the catalogue that prints gives back what it printed, through fn() as
	 * through its own name.
	 *
	 * @return void
	 */
	public function testFnGivesBackWhatAFunctionPrints(): void {
		$this->assertSame( 'https://example.test/?p=7', WordPressExtension::call( 'the_permalink', 7 ) );
	}

	/**
	 * A template hands over strings, and a function that declares an int receives one: the
	 * call is coerced as a theme file or a compiled template coerces it, whatever the strict
	 * types of the file that makes it.
	 *
	 * @return void
	 */
	public function testFnCoercesArgumentsAsATemplateWould(): void {
		$this->assertSame( '3 items', WordPressExtension::call( 'offsetwp_test_count', '3' ) );
	}

	/**
	 * A getter that prints a notice on the way to its value: the notice goes to the output,
	 * where PHP would have put it, and the value comes back untouched.
	 *
	 * @return void
	 */
	public function testFnHandsANoticeOnAndGivesBackTheValue(): void {
		$this->expectOutputString( 'Deprecated: offsetwp_test_terms() is deprecated.' );

		$this->assertSame( array( 'News', 'Events' ), WordPressExtension::call( 'offsetwp_test_terms' ) );
	}

	/**
	 * A buffer the function left open is closed with it, and what was printed into it is
	 * part of what it printed.
	 *
	 * @return void
	 */
	public function testFnClosesTheBuffersAFunctionLeftOpen(): void {
		$level = ob_get_level();

		$this->assertSame( 'Before. Inside.', WordPressExtension::call( 'offsetwp_test_buffered' ) );
		$this->assertSame( $level, ob_get_level() );
	}

	/**
	 * A function that fails half way leaves no buffer behind and nothing on the output, and
	 * its failure reaches the caller as it was raised.
	 *
	 * @return void
	 */
	public function testFnLeavesNoBufferBehindWhenTheFunctionFails(): void {
		$level  = ob_get_level();
		$failed = null;

		// Caught as anything at all, since nothing in the signature of fn() can tell which
		// failure a function of a plugin raises: the class is what is asserted below.
		try {
			WordPressExtension::call( 'offsetwp_test_broken' );
		} catch ( \Throwable $failure ) {
			$failed = $failure;
		}

		$this->assertInstanceOf( \RuntimeException::class, $failed );
		$this->assertSame( 'The plugin failed half way.', $failed->getMessage() );
		$this->assertSame( $level, ob_get_level() );
	}

	/**
	 * A name no function goes by is refused, and the message says what to check.
	 *
	 * @return void
	 */
	public function testFnRefusesANameNoFunctionGoesBy(): void {
		$this->expectException( \BadFunctionCallException::class );
		$this->expectExceptionMessage( 'fn() calls a function by its name, and no function is named "offsetwp_test_missing".' );

		WordPressExtension::call( 'offsetwp_test_missing' );
	}

	/**
	 * A static method is not a function, and is refused as one that does not exist.
	 *
	 * @return void
	 */
	public function testFnRefusesAMethod(): void {
		$this->expectException( \BadFunctionCallException::class );
		$this->expectExceptionMessage( 'no function is named "DateTime::createFromFormat"' );

		WordPressExtension::call( 'DateTime::createFromFormat', 'Y', '2026' );
	}

	/**
	 * A function of PHP itself is refused before anything is called.
	 *
	 * @return void
	 */
	public function testFnRefusesAFunctionOfPhp(): void {
		$this->expectException( \BadFunctionCallException::class );
		$this->expectExceptionMessage( '"exec" is a function of PHP itself' );

		WordPressExtension::call( 'exec', 'echo nothing' );
	}

	/**
	 * One function of the extension, by name.
	 *
	 * @param string $name The name templates call it by.
	 * @return TwigFunction
	 */
	private function declared( string $name ): TwigFunction {
		foreach ( ( new WordPressExtension() )->getFunctions() as $function ) {
			if ( $name === $function->getName() ) {
				return $function;
			}
		}

		$this->fail( sprintf( 'The extension declares no function named %s.', $name ) );
	}
}
