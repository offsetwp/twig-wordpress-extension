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
 * The active theme, as a template reads it: `{{ theme.name }}`, `{{ theme.url }}`,
 * `{{ theme.get('TextDomain') }}`.
 *
 * Behind it is the WP_Theme that wp_get_theme() gives. It is asked of WordPress the first
 * time a method needs it, not when the object is built, and kept from then on. Twig builds
 * the variables of an extension once, on the first render, and asking for the theme then
 * would mean asking before a template needs it, whenever that first render happens.
 *
 * Its parent, when it has one, is a Theme of its own: `{{ theme.parent.url }}` is the
 * address of the parent theme.
 *
 * The class is open. A project extends it with what it needs, reaches the WP_Theme through
 * model(), and registers its own class as the global "theme" of its environment.
 */
class Theme {

	use Encoded;

	/**
	 * A theme, or the active theme when none is given, asked of WordPress when first needed.
	 *
	 * @param \WP_Theme|null $model The theme.
	 */
	public function __construct( private ?\WP_Theme $model = null ) {}

	/**
	 * The name of the theme: the Name header of its style.css, marked safe.
	 *
	 * @return Markup|string
	 */
	public function name(): Markup|string {
		$name = $this->model()->get( 'Name' );

		return self::encoded( is_string( $name ) ? $name : '' );
	}

	/**
	 * The version of the theme: the Version header of its style.css.
	 *
	 * @return string
	 */
	public function version(): string {
		$version = $this->model()->get( 'Version' );

		return is_string( $version ) ? $version : '';
	}

	/**
	 * The directory name of the theme, such as "my-theme": WP_Theme::get_stylesheet().
	 *
	 * @return string
	 */
	public function slug(): string {
		return $this->model()->get_stylesheet();
	}

	/**
	 * The address of the directory of the theme: WP_Theme::get_stylesheet_directory_uri().
	 *
	 * For the active theme, it is the address get_stylesheet_directory_uri() gives, which is
	 * also get_template_directory_uri() when the theme is not a child theme.
	 *
	 * @return string
	 */
	public function url(): string {
		return $this->model()->get_stylesheet_directory_uri();
	}

	/**
	 * The parent theme of a child theme, and null for any other theme.
	 *
	 * @return self|null
	 */
	public function parent(): ?self {
		$parent = $this->model()->parent();

		return false === $parent ? null : new self( $parent );
	}

	/**
	 * A header of the style.css of the theme, as WP_Theme::get() gives it: TextDomain,
	 * Author, a header a plugin declares through the extra_theme_headers filter, or false
	 * when the theme has no such header. Tags is a list.
	 *
	 * @param string $header The name of the header.
	 * @return string|string[]|false
	 */
	public function get( string $header ): string|array|false {
		return $this->model()->get( $header );
	}

	/**
	 * The WP_Theme behind the variable, asked of WordPress the first time it is needed.
	 *
	 * @return \WP_Theme
	 */
	protected function model(): \WP_Theme {
		return $this->model ??= wp_get_theme();
	}
}
