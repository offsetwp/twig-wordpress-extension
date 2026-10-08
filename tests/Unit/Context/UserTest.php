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
use OffsetWP\Twig\Extension\WordPressExtension\Context\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Twig\Markup;

/**
 * What each method of the user variable reads of the user, and when the user is asked of
 * WordPress.
 */
#[CoversClass( User::class )]
#[CoversTrait( Encoded::class )]
final class UserTest extends TestCase {

	/**
	 * Building the variable asks nothing of WordPress. The user who is logged in is asked for
	 * the first time a method needs them, and once only.
	 *
	 * @return void
	 */
	public function testTheUserIsAskedForWhenFirstNeededAndOnce(): void {
		$model = new \ReflectionProperty( User::class, 'model' );

		$this->assertNull( $model->getValue( new User() ) );

		$user = new User();

		$this->assertTrue( $user->logged_in() );

		$asked = $model->getValue( $user );

		$this->assertInstanceOf( \WP_User::class, $asked );
		$this->assertSame( 7, $user->id() );
		$this->assertSame( $asked, $model->getValue( $user ) );
	}

	/**
	 * The display name is handed over as WordPress keeps it, encoded, and marked safe.
	 *
	 * @return void
	 */
	public function testTheNameIsMarkedSafeAsWordPressKeepsIt(): void {
		$name = ( new User() )->name();

		$this->assertInstanceOf( Markup::class, $name );
		$this->assertSame( 'Ada &amp; co', (string) $name );
	}

	/**
	 * The email address, the roles, the capabilities, the avatar, the address of the posts
	 * and a meta are what WordPress gives for the user.
	 *
	 * @return void
	 */
	public function testEachMethodGivesWhatWordPressGivesForTheUser(): void {
		$user = new User();

		$this->assertSame( 'ada@example.test', $user->email() );
		$this->assertSame( array( 'editor' ), $user->roles() );
		$this->assertTrue( $user->can( 'edit_posts' ) );
		$this->assertFalse( $user->can( 'manage_options' ) );
		$this->assertSame( 'https://example.test/avatar/7?s=96', $user->avatar() );
		$this->assertSame( 'https://example.test/avatar/7?s=32', $user->avatar( 32 ) );
		$this->assertSame( 'https://example.test/?author=7', $user->link() );
		$this->assertSame( '+44 20 7946 0000', $user->meta( 'phone' ) );
		$this->assertSame( '', $user->meta( 'fax' ) );
	}

	/**
	 * Nobody logged in, the user is the one of ID 0 that WordPress gives: it is not logged in,
	 * and has no name.
	 *
	 * @return void
	 */
	public function testNobodyLoggedInIsTheUserOfIdZero(): void {
		$user = new User( new \WP_User() );

		$this->assertFalse( $user->logged_in() );
		$this->assertSame( 0, $user->id() );
		$this->assertSame( '', $user->name() );
		$this->assertSame( array(), $user->roles() );
	}
}
