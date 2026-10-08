<?php
/**
 * OffsetWP Twig WordPress Extension
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension\WordPressExtension\Context
 */

declare( strict_types=1 );

namespace OffsetWP\Twig\Extension\WordPressExtension\Context;

use Twig\Markup;

/**
 * The user who is logged in, as a template reads them: `{% if user.logged_in %}`,
 * `{{ user.name }}`, `{{ user.avatar(32) }}`.
 *
 * Behind it is the WP_User that wp_get_current_user() gives. It is asked of WordPress the
 * first time a method needs it, not when the object is built, and kept from then on. Twig
 * builds the variables of an extension once, on the first render, which can happen before
 * WordPress has determined who is logged in.
 *
 * Nobody logged in, WordPress gives the user of ID 0, and so does this object: it exists all
 * the same, so `{% if user %}` always holds, and `{% if user.logged_in %}` is the question
 * to ask. A function of WordPress that takes a user is given its ID: `user.id`.
 *
 * The class is open. A project extends it with what it needs, reaches the WP_User through
 * model(), and registers its own class as the global "user" of its environment.
 */
class User {

	use Encoded;

	/**
	 * A user, or the user who is logged in when none is given, asked of WordPress when first
	 * needed.
	 *
	 * @param \WP_User|null $model The user.
	 */
	public function __construct( private ?\WP_User $model = null ) {}

	/**
	 * Whether anybody is logged in: WP_User::exists().
	 *
	 * @return bool
	 */
	public function logged_in(): bool {
		return $this->model()->exists();
	}

	/**
	 * The ID of the user, and 0 when nobody is logged in.
	 *
	 * @return int
	 */
	public function id(): int {
		return $this->model()->ID;
	}

	/**
	 * The name the user chose to display, marked safe.
	 *
	 * @return Markup|string
	 */
	public function name(): Markup|string {
		return self::encoded( $this->model()->display_name );
	}

	/**
	 * The email address of the user.
	 *
	 * @return string
	 */
	public function email(): string {
		return $this->model()->user_email;
	}

	/**
	 * The roles of the user, by slug: WP_User::$roles.
	 *
	 * @return string[]
	 */
	public function roles(): array {
		return $this->model()->roles;
	}

	/**
	 * Whether the user has a capability: user_can( $user, $capability, ...$args ).
	 *
	 * @param string $capability The capability, or the name of a meta capability.
	 * @param mixed  ...$args    What the meta capability takes, such as the ID of a post.
	 * @return bool
	 */
	public function can( string $capability, mixed ...$args ): bool {
		return user_can( $this->model(), $capability, ...$args );
	}

	/**
	 * The address of the avatar of the user: get_avatar_url( $user, array( 'size' => $size ) ).
	 *
	 * @param int $size The size of the image, in pixels.
	 * @return string|false
	 */
	public function avatar( int $size = 96 ): string|false {
		return get_avatar_url( $this->model(), array( 'size' => $size ) );
	}

	/**
	 * The address of the posts of the user: get_author_posts_url( $id ).
	 *
	 * @return string
	 */
	public function link(): string {
		return get_author_posts_url( $this->model()->ID );
	}

	/**
	 * A meta of the user, as WordPress stores it: get_user_meta( $id, $key, true ).
	 *
	 * It is given back as it is, and an empty string when the user has no such meta. It
	 * holds whatever was saved into it, so it is not marked safe.
	 *
	 * @param string $key The key of the meta.
	 * @return mixed
	 */
	public function meta( string $key ): mixed {
		return get_user_meta( $this->model()->ID, $key, true );
	}

	/**
	 * The WP_User behind the variable, asked of WordPress the first time it is needed.
	 *
	 * @return \WP_User
	 */
	protected function model(): \WP_User {
		return $this->model ??= wp_get_current_user();
	}
}
