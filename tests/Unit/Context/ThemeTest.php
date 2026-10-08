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
use OffsetWP\Twig\Extension\WordPressExtension\Context\Theme;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Twig\Markup;

/**
 * What each method of the theme variable reads of the theme, and when the theme is asked of
 * WordPress.
 */
#[CoversClass( Theme::class )]
#[CoversTrait( Encoded::class )]
final class ThemeTest extends TestCase {

	/**
	 * Building the variable asks nothing of WordPress. The active theme is asked for the first
	 * time a method needs it, and once only.
	 *
	 * @return void
	 */
	public function testTheActiveThemeIsAskedForWhenFirstNeededAndOnce(): void {
		$model = new \ReflectionProperty( Theme::class, 'model' );

		$this->assertNull( $model->getValue( new Theme() ) );

		$theme = new Theme();

		$this->assertSame( 'fish-and-chips', $theme->slug() );

		$asked = $model->getValue( $theme );

		$this->assertInstanceOf( \WP_Theme::class, $asked );
		$this->assertSame( '2.0.0', $theme->version() );
		$this->assertSame( $asked, $model->getValue( $theme ) );
	}

	/**
	 * The name is handed over as WordPress reads it, encoded, and marked safe.
	 *
	 * @return void
	 */
	public function testTheNameIsMarkedSafeAsWordPressReadsIt(): void {
		$name = ( new Theme() )->name();

		$this->assertInstanceOf( Markup::class, $name );
		$this->assertSame( 'Fish &amp; Chips', (string) $name );
	}

	/**
	 * The address is the one of the directory of the theme, and a header is read as
	 * WordPress reads it, false when the theme has none.
	 *
	 * @return void
	 */
	public function testTheAddressAndTheHeadersAreWhatWordPressGives(): void {
		$theme = new Theme();

		$this->assertSame( 'https://example.test/wp-content/themes/fish-and-chips', $theme->url() );
		$this->assertSame( 'fish-and-chips', $theme->get( 'TextDomain' ) );
		$this->assertFalse( $theme->get( 'Author' ) );
	}

	/**
	 * The parent of a child theme is a theme variable of its own, and a theme that is no
	 * child has none.
	 *
	 * @return void
	 */
	public function testTheParentOfAChildThemeIsAThemeOfItsOwn(): void {
		$parent = ( new Theme() )->parent();

		$this->assertInstanceOf( Theme::class, $parent );
		$this->assertSame( 'https://example.test/wp-content/themes/chippy', $parent->url() );
		$this->assertSame( '1.4.0', $parent->version() );
		$this->assertNull( $parent->parent() );
	}

	/**
	 * A theme given to the variable is the one it reads, and WordPress is asked for nothing.
	 *
	 * @return void
	 */
	public function testAGivenThemeIsTheOneRead(): void {
		$theme = new Theme( new \WP_Theme( 'brasserie', array( 'Name' => '' ) ) );

		$this->assertSame( 'brasserie', $theme->slug() );
		$this->assertSame( '', $theme->name() );
		$this->assertSame( '', $theme->version() );
	}
}
