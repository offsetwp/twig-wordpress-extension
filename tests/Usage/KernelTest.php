<?php
/**
 * OffsetWP Twig WordPress Extension Tests
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension\WordPressExtension\Tests\Usage
 */

declare( strict_types=1 );

namespace OffsetWP\Twig\Extension\WordPressExtension\Tests\Usage;

use OffsetWP\Bundle\TwigBundle\Twig;
use OffsetWP\Framework\Kernel;
use OffsetWP\Support\Env;
use OffsetWP\Twig\Extension\WordPressExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * The installation the README describes, in a whole project: the bundle in
 * config/bundles.php, one line in config/packages/twig.php, and a template. The kernel is
 * built by the same fluent call a project makes, and nothing here reaches into the container
 * or adds the extension by hand.
 */
#[CoversClass( WordPressExtension::class )]
final class KernelTest extends TestCase {

	/**
	 * Booting a project hands its container to the facade of the bundle, which is static and
	 * outlives the test. It is cleared on both ends.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Twig::reset();
	}

	/**
	 * And nothing is left behind for the next test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		Twig::reset();

		parent::tearDown();
	}

	/**
	 * The template of the project calls the functions of WordPress, and one of a plugin.
	 *
	 * @return void
	 */
	public function testAProjectThatAddsTheExtensionRendersTheFunctionsOfWordPress(): void {
		$root = dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'Site';

		$kernel = Kernel::configure( $root )
			->environment( Env::PRODUCTION )
			->config( $root . DIRECTORY_SEPARATOR . 'config' )
			->boot();

		$this->assertSame(
			'<head><meta name="generator" content="WordPress"></head>' . "\n"
			. '<a href="https://example.test/?p=7">Fish &amp; <em>Chips</em> #7</a>' . "\n"
			. 'Hello, Ada!' . "\n",
			Twig::of( $kernel )->render( 'page.twig', array( 'post' => 7 ) )
		);
	}
}
