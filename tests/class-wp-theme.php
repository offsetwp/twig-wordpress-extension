<?php
/**
 * OffsetWP Twig WordPress Extension Tests
 *
 * The class of WordPress a theme is, standing in for the platform a test process does not
 * have. It keeps the name of the class and of the methods the theme variable calls, with
 * what they hand back, and nothing else: no style.css is read, and a test builds the theme
 * it wants from its headers.
 *
 * The declaration is guarded, so that a process which does have the platform loaded keeps
 * the platform's own. The analyser reads this file too, so it is also where the signatures
 * of those methods come from.
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension\WordPressExtension\Tests
 */

declare( strict_types=1 );

if ( ! class_exists( 'WP_Theme', false ) ) {
	/**
	 * A theme: its directory name, the headers of its style.css, and its parent.
	 */
	class WP_Theme {

		/**
		 * The directory name of the theme.
		 *
		 * @var string
		 */
		private $stylesheet;

		/**
		 * The headers of its style.css, by name.
		 *
		 * @var array<string, string|string[]>
		 */
		private $headers;

		/**
		 * The parent theme, or false for a theme that is not a child theme.
		 *
		 * @var WP_Theme|false
		 */
		private $parent;

		/**
		 * A theme, built from what its style.css would say.
		 *
		 * @param string                         $stylesheet   The directory name of the theme.
		 * @param array<string, string|string[]> $headers      The headers of its style.css.
		 * @param WP_Theme|null                  $parent_theme The parent theme, for a child theme.
		 */
		public function __construct( string $stylesheet, array $headers, ?WP_Theme $parent_theme = null ) {
			$this->stylesheet = $stylesheet;
			$this->headers    = $headers;
			$this->parent     = $parent_theme ?? false;
		}

		/**
		 * Returns a header of the style.css of the theme, or false when it has none.
		 *
		 * @param string $header The name of the header.
		 * @return string|string[]|false
		 */
		public function get( $header ) {
			return $this->headers[ $header ] ?? false;
		}

		/**
		 * Returns the directory name of the theme.
		 *
		 * @return string
		 */
		public function get_stylesheet() {
			return $this->stylesheet;
		}

		/**
		 * Returns the address of the directory of the theme.
		 *
		 * @return string
		 */
		public function get_stylesheet_directory_uri() {
			return 'https://example.test/wp-content/themes/' . $this->stylesheet;
		}

		/**
		 * Returns the parent theme, or false for a theme that is not a child theme.
		 *
		 * @return WP_Theme|false
		 */
		public function parent() {
			return $this->parent;
		}
	}
}
