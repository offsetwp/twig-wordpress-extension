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
 * The site, as a template reads it: `{{ site.name }}`, `{{ site.url }}`,
 * `{{ site.option('date_format') }}`.
 *
 * Each method is one call of WordPress, made when a template calls it. A single install of
 * WordPress has no object for its site, so nothing is kept here: what a template reads is
 * what WordPress says at that moment. Whatever else get_bloginfo() knows, the address of a
 * feed say, is one function away in a template.
 *
 * The class is open. A project extends it with what it needs and registers its own class
 * as the global "site" of its environment, which takes the place of this one.
 */
class Site {

	use Encoded;

	/**
	 * The name of the site: get_bloginfo( 'name' ), marked safe.
	 *
	 * @return Markup|string
	 */
	public function name(): Markup|string {
		return self::encoded( get_bloginfo( 'name' ) );
	}

	/**
	 * The tagline of the site: get_bloginfo( 'description' ), marked safe, and an empty
	 * string when the site has none.
	 *
	 * @return Markup|string
	 */
	public function description(): Markup|string {
		return self::encoded( get_bloginfo( 'description' ) );
	}

	/**
	 * The address of the home page, or of a path below it: home_url( $path ).
	 *
	 * @param string $path A path relative to the home page.
	 * @return string
	 */
	public function url( string $path = '' ): string {
		return home_url( $path );
	}

	/**
	 * The locale of the site, such as "fr_FR": get_locale().
	 *
	 * @return string
	 */
	public function locale(): string {
		return get_locale();
	}

	/**
	 * The language of the site in the form the lang attribute takes, such as "fr-FR":
	 * get_bloginfo( 'language' ).
	 *
	 * @return string
	 */
	public function language(): string {
		return get_bloginfo( 'language' );
	}

	/**
	 * The character set of the site: get_bloginfo( 'charset' ).
	 *
	 * @return string
	 */
	public function charset(): string {
		return get_bloginfo( 'charset' );
	}

	/**
	 * An option, as WordPress stores it: get_option( $option, $default_value ).
	 *
	 * It is given back as it is: a string, a number or an array. It holds whatever was saved
	 * into it, so it is not marked safe, and a template prints it escaped like any value.
	 *
	 * @param string $option        The name of the option.
	 * @param mixed  $default_value What to give back when the option does not exist.
	 * @return mixed
	 */
	public function option( string $option, mixed $default_value = false ): mixed {
		return get_option( $option, $default_value );
	}
}
