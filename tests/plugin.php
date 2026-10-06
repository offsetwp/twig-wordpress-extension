<?php
/**
 * OffsetWP Twig WordPress Extension Tests
 *
 * The functions of a plugin, which no catalogue lists and fn() calls by their names. Each one
 * stands for a way a function of a plugin can behave that fn() has to give back faithfully:
 * returning a value, printing a notice on the way to returning one, leaving an output buffer
 * open, and failing half way through what it prints.
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension\WordPressExtension\Tests
 */

declare( strict_types=1 );

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- These print fixed text, which is what the tests read back.

if ( ! function_exists( 'offsetwp_test_greet' ) ) {
	/**
	 * Returns a greeting.
	 *
	 * @param string $name     Who to greet.
	 * @param string $greeting What to greet them with.
	 * @return string
	 */
	function offsetwp_test_greet( string $name, string $greeting = 'Hello' ): string {
		return $greeting . ', ' . $name . '!';
	}
}

if ( ! function_exists( 'offsetwp_test_count' ) ) {
	/**
	 * Returns how many items a list has, which is a count a template might read from a
	 * query string: a typed parameter, handed a string that holds a number.
	 *
	 * @param int $items How many items.
	 * @return string
	 */
	function offsetwp_test_count( int $items ): string {
		return $items . ' items';
	}
}

if ( ! function_exists( 'offsetwp_test_terms' ) ) {
	/**
	 * Returns a list of terms, and prints a deprecation notice on the way, the way a getter
	 * does while debugging is displayed.
	 *
	 * @return list<string>
	 */
	function offsetwp_test_terms(): array {
		echo 'Deprecated: offsetwp_test_terms() is deprecated.';

		return array( 'News', 'Events' );
	}
}

if ( ! function_exists( 'offsetwp_test_buffered' ) ) {
	/**
	 * Prints a line, then opens an output buffer it never closes, and prints into it.
	 *
	 * @return void
	 */
	function offsetwp_test_buffered(): void {
		echo 'Before. ';

		ob_start();

		echo 'Inside.';
	}
}

if ( ! function_exists( 'offsetwp_test_broken' ) ) {
	/**
	 * Prints half of its markup, opens a buffer of its own, and fails.
	 *
	 * @throws \RuntimeException Always.
	 * @return void
	 */
	function offsetwp_test_broken(): void {
		echo '<div>';

		ob_start();

		echo 'Half';

		throw new \RuntimeException( 'The plugin failed half way.' );
	}
}
