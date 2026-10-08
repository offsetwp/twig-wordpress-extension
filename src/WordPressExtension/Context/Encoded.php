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
 * Text WordPress keeps encoded, handed to Twig so that it is printed once.
 *
 * WordPress encodes some text before a template ever reads it. The name and the tagline of
 * the site go through esc_html() when they are saved, the display name of a user through
 * _wp_specialchars(), and the name of a theme through wp_kses() when it is read. Printed as
 * a plain string, that text would be encoded a second time by Twig, and "L'Atelier" would
 * read "L&#039;Atelier" on the page. Marked safe, it is printed the way WordPress prints it,
 * as the functions of this extension are.
 *
 * Empty text is an empty string rather than an empty Markup, which is what Twig itself does
 * for a {% set %} block that captures nothing. Before Twig 3.28, an empty Markup is true to
 * "and", "or" and the ternary operator, so `{% if site.description and … %}` would hold for
 * a site without a tagline.
 */
trait Encoded {

	/**
	 * The text marked safe, or an empty string.
	 *
	 * @param string $text Text as WordPress keeps it, encoded.
	 * @return Markup|string
	 */
	private static function encoded( string $text ): Markup|string {
		return '' === $text ? '' : new Markup( $text, get_bloginfo( 'charset' ) );
	}
}
