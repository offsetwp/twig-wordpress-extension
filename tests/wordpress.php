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
 * wp_footer() is left out on purpose: it is the function of the catalogue a test calls to
 * see what a template is told when WordPress has not declared what it calls.
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
