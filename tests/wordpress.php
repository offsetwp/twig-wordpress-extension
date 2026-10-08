<?php
/**
 * OffsetWP Twig WordPress Extension Tests
 *
 * The functions of WordPress the tests call, standing in for the platform a test process
 * does not have. src/ hands Twig the names of these functions exactly as it does in a
 * request, and these are what answer.
 *
 * Each one keeps the name, the parameters and the way of handing back its result of the
 * function it stands in for — which is the whole of what this extension depends on. One
 * prints, another returns, a third prints and returns the same markup, a fourth prints and
 * returns a boolean, and a fifth prints or returns as an argument says. Two more load a
 * template of the theme, header.php or footer.php, and print what it prints. What they print
 * is fixed and short, so that an assertion can spell it out.
 *
 * The others describe the site, the theme and the user the variables of the extension read:
 * a site called Fish & Chips, kept encoded as WordPress keeps it, with no tagline; a child
 * theme of the same name; and Ada, who is logged in. wp_get_theme() and wp_get_current_user()
 * build a new object on each call, so that a test can tell whether a variable asked twice.
 *
 * wp_footer() and wp_get_document_title() are left out on purpose: they are the functions of
 * the catalogue, one that prints and one that returns, that a test calls to see what a
 * template is told when WordPress has not declared what it calls.
 *
 * Every declaration is guarded, so that a process which does have the platform loaded
 * keeps the platform's own. The analyser reads this file too, so it is also where the
 * signatures of those functions come from.
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension\WordPressExtension\Tests
 */

declare( strict_types=1 );

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- These print fixed markup, the way the functions they stand in for print theirs.

if ( ! function_exists( 'wp_head' ) ) {
	/**
	 * Prints what the plugins and the theme hook into the head of the document.
	 *
	 * @return void
	 */
	function wp_head(): void {
		echo '<meta name="generator" content="WordPress">';
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Returns the translation of a text, which in this process is the text itself.
	 *
	 * @param string $text   The text to translate.
	 * @param string $domain The text domain.
	 * @return string
	 */
	function __( string $text, string $domain = 'default' ): string { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionDoubleUnderscore -- The name is the platform's.
		unset( $domain );

		return $text;
	}
}

if ( ! function_exists( 'the_permalink' ) ) {
	/**
	 * Prints the address of a post.
	 *
	 * @param int $post The ID of the post.
	 * @return void
	 */
	function the_permalink( int $post = 0 ): void {
		echo 'https://example.test/?p=' . $post;
	}
}

if ( ! function_exists( 'get_the_title' ) ) {
	/**
	 * Returns the title of a post, as WordPress prepares it for display: texturized, and
	 * with the markup an editor may put in a title.
	 *
	 * @param int $post The ID of the post.
	 * @return string
	 */
	function get_the_title( int $post = 0 ): string {
		return 'Fish &amp; <em>Chips</em> #' . $post;
	}
}

if ( ! function_exists( 'the_title' ) ) {
	/**
	 * Prints the title of the current post between two strings, or returns it when told not
	 * to print.
	 *
	 * @param string $before  What goes before the title.
	 * @param string $after   What goes after the title.
	 * @param bool   $display Whether to print the title rather than return it.
	 * @return string|null
	 */
	function the_title( string $before = '', string $after = '', bool $display = true ): ?string {
		$title = $before . 'Fish &amp; <em>Chips</em>' . $after;

		if ( ! $display ) {
			return $title;
		}

		echo $title;

		return null;
	}
}

if ( ! function_exists( 'get_the_post_thumbnail' ) ) {
	/**
	 * Returns the image tag of the featured image of a post.
	 *
	 * @param int    $post The ID of the post.
	 * @param string $size The registered size of the image.
	 * @param string $attr The class of the tag.
	 * @return string
	 */
	function get_the_post_thumbnail( int $post = 0, string $size = 'post-thumbnail', string $attr = '' ): string {
		return sprintf( '<img src="/%d-%s.jpg" class="%s">', $post, $size, $attr );
	}
}

if ( ! function_exists( 'wp_link_pages' ) ) {
	/**
	 * Prints the links to the pages of a post split with <!--nextpage-->, and returns them
	 * as well, which is what the platform does.
	 *
	 * @return string
	 */
	function wp_link_pages(): string {
		$html = '<nav class="post-pages">1 2</nav>';

		echo $html;

		return $html;
	}
}

if ( ! function_exists( 'dynamic_sidebar' ) ) {
	/**
	 * Prints the widgets of a sidebar, and says whether it had any.
	 *
	 * @param string $index The ID of the sidebar.
	 * @return bool
	 */
	function dynamic_sidebar( string $index = 'sidebar-1' ): bool {
		if ( 'footer' !== $index ) {
			return false;
		}

		echo '<aside class="widget">Hello</aside>';

		return true;
	}
}

if ( ! function_exists( 'get_header' ) ) {
	/**
	 * Loads the header template of the theme, which prints the header of the site:
	 * header-{name}.php when the theme has one, header.php otherwise. This theme has
	 * header.php and header-shop.php.
	 *
	 * @param string|null          $name The name of the specialized header.
	 * @param array<string, mixed> $args What the template is handed.
	 * @return void
	 */
	function get_header( ?string $name = null, array $args = array() ): void {
		unset( $args );

		echo 'shop' === $name ? '<header class="shop">Shop</header>' : '<header>Fish &amp; Chips</header>';
	}
}

if ( ! function_exists( 'get_footer' ) ) {
	/**
	 * Loads the footer template of the theme, which prints the footer of the site: footer.php,
	 * the only one this theme has.
	 *
	 * @param string|null          $name The name of the specialized footer.
	 * @param array<string, mixed> $args What the template is handed.
	 * @return void
	 */
	function get_footer( ?string $name = null, array $args = array() ): void {
		unset( $name, $args );

		echo '<footer>Fish &amp; Chips, since 1860</footer>';
	}
}

if ( ! function_exists( 'wp_nav_menu' ) ) {
	/**
	 * Prints the menu set to a location, returns it when told not to print, and hands back
	 * false when no menu is set there.
	 *
	 * @param array{theme_location?: string, echo?: bool} $args The location, and whether to print.
	 * @return string|false|null
	 */
	function wp_nav_menu( array $args = array() ): string|false|null {
		if ( 'primary' !== ( $args['theme_location'] ?? 'primary' ) ) {
			return false;
		}

		$menu = '<nav class="menu"><a href="/">Home</a></nav>';

		if ( false === ( $args['echo'] ?? true ) ) {
			return $menu;
		}

		echo $menu;

		return null;
	}
}

if ( ! function_exists( 'get_bloginfo' ) ) {
	/**
	 * Returns what WordPress knows of the site: its name, kept encoded as WordPress keeps
	 * it, no tagline, its language and its character set. Any other key is the name, as it
	 * is in WordPress.
	 *
	 * @param string $show   What to return.
	 * @param string $filter Whether to filter the value for display.
	 * @return string
	 */
	function get_bloginfo( string $show = '', string $filter = 'raw' ): string {
		unset( $filter );

		return match ( $show ) {
			'description' => '',
			'language'    => 'en-GB',
			'charset'     => 'UTF-8',
			default       => 'Fish &amp; Chips',
		};
	}
}

if ( ! function_exists( 'home_url' ) ) {
	/**
	 * Returns the address of the home page, or of a path below it.
	 *
	 * @param string      $path   A path relative to the home page.
	 * @param string|null $scheme The scheme to give the address.
	 * @return string
	 */
	function home_url( string $path = '', ?string $scheme = null ): string {
		unset( $scheme );

		return 'https://example.test' . ( '' === $path ? '' : '/' . ltrim( $path, '/' ) );
	}
}

if ( ! function_exists( 'get_locale' ) ) {
	/**
	 * Returns the locale of the site.
	 *
	 * @return string
	 */
	function get_locale(): string {
		return 'en_GB';
	}
}

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Returns an option: a date format, and a motto saved as it was typed, markup and all.
	 *
	 * @param string $option        The name of the option.
	 * @param mixed  $default_value What to return when the option does not exist.
	 * @return mixed
	 */
	function get_option( string $option, mixed $default_value = false ): mixed {
		return match ( $option ) {
			'date_format' => 'j F Y',
			'motto'       => '<b>Fresh</b> & hot',
			default       => $default_value,
		};
	}
}

if ( ! function_exists( 'wp_get_theme' ) ) {
	/**
	 * Returns the active theme, Fish & Chips, a child theme of Chippy, as a new object on
	 * each call.
	 *
	 * @param string $stylesheet The directory name of a theme, or nothing for the active one.
	 * @param string $theme_root The directory the theme is in.
	 * @return WP_Theme
	 */
	function wp_get_theme( string $stylesheet = '', string $theme_root = '' ): WP_Theme {
		unset( $stylesheet, $theme_root );

		return new WP_Theme(
			'fish-and-chips',
			array(
				'Name'       => 'Fish &amp; Chips',
				'Version'    => '2.0.0',
				'TextDomain' => 'fish-and-chips',
			),
			new WP_Theme(
				'chippy',
				array(
					'Name'    => 'Chippy',
					'Version' => '1.4.0',
				)
			)
		);
	}
}

if ( ! function_exists( 'wp_get_current_user' ) ) {
	/**
	 * Returns the user who is logged in, Ada, an editor whose display name WordPress keeps
	 * encoded, as a new object on each call.
	 *
	 * @return WP_User
	 */
	function wp_get_current_user(): WP_User {
		return new WP_User(
			7,
			array(
				'display_name' => 'Ada &amp; co',
				'user_email'   => 'ada@example.test',
			),
			array( 'editor' )
		);
	}
}

if ( ! function_exists( 'user_can' ) ) {
	/**
	 * Says whether a user has a capability: an editor can edit posts, and nothing more.
	 *
	 * @param int|WP_User $user       The user, or its ID.
	 * @param string      $capability The capability.
	 * @param mixed       ...$args    What a meta capability takes.
	 * @return bool
	 */
	function user_can( int|WP_User $user, string $capability, mixed ...$args ): bool {
		unset( $args );

		return $user instanceof WP_User && in_array( 'editor', $user->roles, true ) && 'edit_posts' === $capability;
	}
}

if ( ! function_exists( 'get_avatar_url' ) ) {
	/**
	 * Returns the address of the avatar of a user, at the size asked for, and false for
	 * anything but a user, where WordPress would look further.
	 *
	 * @param mixed                     $id_or_email The user, its ID or its email address.
	 * @param array<string, mixed>|null $args        The size of the image, among others.
	 * @return string|false
	 */
	function get_avatar_url( mixed $id_or_email, ?array $args = null ): string|false {
		if ( ! $id_or_email instanceof WP_User ) {
			return false;
		}

		$size = is_int( $args['size'] ?? null ) ? $args['size'] : 96;

		return sprintf( 'https://example.test/avatar/%d?s=%d', $id_or_email->ID, $size );
	}
}

if ( ! function_exists( 'get_author_posts_url' ) ) {
	/**
	 * Returns the address of the posts of an author.
	 *
	 * @param int    $author_id       The ID of the author.
	 * @param string $author_nicename The slug of the author.
	 * @return string
	 */
	function get_author_posts_url( int $author_id, string $author_nicename = '' ): string {
		unset( $author_nicename );

		return 'https://example.test/?author=' . $author_id;
	}
}

if ( ! function_exists( 'get_user_meta' ) ) {
	/**
	 * Returns a meta of a user: the phone number of Ada, and an empty string for any other,
	 * which is what WordPress gives for a single meta a user does not have.
	 *
	 * @param int    $user_id The ID of the user.
	 * @param string $key     The key of the meta.
	 * @param bool   $single  Whether to return one value rather than all of them.
	 * @return mixed
	 */
	function get_user_meta( int $user_id, string $key = '', bool $single = false ): mixed {
		unset( $single );

		return 7 === $user_id && 'phone' === $key ? '+44 20 7946 0000' : '';
	}
}
