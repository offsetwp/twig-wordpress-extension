<?php
/**
 * OffsetWP Twig WordPress Extension
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension\WordPressExtension
 */

declare( strict_types=1 );

namespace OffsetWP\Twig\Extension\WordPressExtension;

/**
 * Where a function of WordPress puts its result: on the output, or in its return value.
 *
 * The name of a function does not always say which. the_title() prints and get_the_title()
 * returns, but selected() prints through a helper it returns from, and the_post() prints
 * nothing at all. Each function of the catalogue says which kind it is, as read in the source
 * of WordPress rather than guessed from its name.
 */
enum Result {

	/**
	 * The function prints its result. What it prints is captured and given back, so a
	 * template reads it as it would read a return value.
	 */
	case Printed;

	/**
	 * The function returns its result. Twig calls it directly, as WordPress declares it.
	 */
	case Returned;
}
