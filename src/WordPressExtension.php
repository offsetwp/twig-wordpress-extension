<?php
/**
 * OffsetWP Twig WordPress Extension
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension
 */

declare( strict_types=1 );

namespace OffsetWP\Twig\Extension;

use OffsetWP\Twig\Extension\WordPressExtension\Catalogue;
use OffsetWP\Twig\Extension\WordPressExtension\Result;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The functions of WordPress, in Twig, under their own names and with their own arguments.
 *
 * Nothing stands between a template and WordPress. A function that returns its result is
 * handed to Twig as it is, so `{{ get_the_title( post ) }}` compiles to a plain call of
 * get_the_title(), checked against the signature WordPress declares and open to its named
 * arguments. A function that prints its result is called the same way, and what it prints is
 * given back to the template rather than left on the output: `{% set link = the_permalink() %}`
 * holds the address, and `{{ the_title()|upper }}` changes the title. Every other function of
 * WordPress, of a plugin or of a theme is one call of fn() away.
 *
 * Every function is marked safe for HTML. What WordPress hands over is printed the way
 * WordPress prints it: `{{ __( 'A & B' ) }}` reads "A & B", and a template escapes where a
 * classic theme would, with esc_html() and the like. Whatever else a template prints, a
 * variable or a property, is escaped or not as the autoescape option of the environment says.
 * That choice belongs to the project that configures Twig, and none of it is made here.
 *
 * Note for anyone editing this file: the namespace is OffsetWP\Twig\Extension, so a partially
 * qualified name such as Twig\TwigFunction would resolve to a class beneath it that does not
 * exist. Every Twig class is imported, always.
 */
final class WordPressExtension extends AbstractExtension {

	/**
	 * The name templates call fn() by.
	 *
	 * @var string
	 */
	public const FN = 'fn';

	/**
	 * The options of a function Twig calls directly.
	 *
	 * @var array{is_safe: list<string>}
	 */
	private const RETURNED = array( 'is_safe' => array( 'html' ) );

	/**
	 * The options of a function whose output is captured: fn(), and every function that
	 * prints.
	 *
	 * Variadic, because the arguments are passed on to a function this extension does not
	 * declare itself. Twig hands a variadic PHP parameter its named arguments under their
	 * names, which then reach WordPress as named arguments of its own function.
	 *
	 * @var array{is_safe: list<string>, is_variadic: true}
	 */
	private const CAPTURED = array(
		'is_safe'     => array( 'html' ),
		'is_variadic' => true,
	);

	/**
	 * {@inheritDoc}
	 *
	 * A function that returns is mapped to itself, name for name. A function that prints is
	 * mapped to a closure that captures it, since the name is all Twig would pass along.
	 *
	 * So is a function that returns but that WordPress has not declared yet. Twig builds this
	 * list once, the first time it needs a function, and a template rendered early enough —
	 * before the pluggable functions, say — would find wp_create_nonce() missing for good:
	 * Twig refuses to compile a call to a function that does not exist, with a message naming
	 * neither WordPress nor the time. Wrapped, the function is looked up again when the
	 * template calls it, and either answers as it would have, having printed nothing, or is
	 * reported missing by its name.
	 *
	 * @return list<TwigFunction>
	 */
	public function getFunctions(): array {
		$functions = array();

		foreach ( Catalogue::FUNCTIONS as $name => $result ) {
			$functions[] = Result::Returned === $result && is_callable( $name )
				? new TwigFunction( $name, $name, self::RETURNED )
				: new TwigFunction( $name, static fn ( mixed ...$arguments ): mixed => self::capture( $name, $arguments ), self::CAPTURED );
		}

		$functions[] = new TwigFunction( self::FN, array( self::class, 'call' ), self::CAPTURED );

		return $functions;
	}

	/**
	 * Call a function by its name: what fn() runs.
	 *
	 * Any function WordPress, a plugin or a theme declares, given as it is declared, and the
	 * same deal as the functions of the catalogue: what it prints is given back, or what it
	 * returns when it prints nothing. A function of PHP itself is refused. Twig has filters and
	 * functions for what PHP does, and a template able to reach exec() or file_put_contents()
	 * through a string is a template able to do anything.
	 *
	 * Example: `{{ fn( 'yoast_breadcrumb', '<nav>', '</nav>' ) }}`
	 *
	 * @param string $function_name The name of the function.
	 * @param mixed  ...$arguments  Its arguments; one under a string key is a named argument.
	 * @throws \BadFunctionCallException When no function goes by that name, or it is one of PHP's own.
	 * @return mixed
	 */
	public static function call( string $function_name, mixed ...$arguments ): mixed {
		if ( ! function_exists( $function_name ) ) {
			throw new \BadFunctionCallException(
				sprintf(
					'fn() calls a function by its name, and no function is named "%s". Check the spelling, and that the plugin or the theme declaring it is active.',
					$function_name
				)
			);
		}

		if ( ( new \ReflectionFunction( $function_name ) )->isInternal() ) {
			throw new \BadFunctionCallException(
				sprintf(
					'fn() calls the functions of WordPress, of its plugins and of its themes, and "%s" is a function of PHP itself. Twig has filters and functions of its own for what PHP does.',
					$function_name
				)
			);
		}

		return self::capture( $function_name, $arguments );
	}

	/**
	 * Call a function, and give back what it printed — or what it returned, when it printed
	 * nothing.
	 *
	 * Some functions of WordPress print and return the same markup — wp_link_pages(),
	 * wp_nonce_field(), selected() — and others print and return a boolean, as
	 * dynamic_sidebar() does. In both cases the markup is given back, once. Asked not to print,
	 * as `wp_nav_menu( { echo: false } )` is, a function prints nothing, and its return value is
	 * given back as it stands: a string, an array, or false.
	 *
	 * A function that prints one thing and returns another is a function that returns, and
	 * printed a notice on the way: a getter called while debugging displays its deprecations.
	 * The notice goes to the output, where PHP would have put it, and the value comes back
	 * untouched.
	 *
	 * The function is called through call_user_func_array() rather than directly. A call made
	 * by an internal function is not subject to the strict types of this file, so the
	 * arguments of a template are coerced the way a theme file or a compiled template coerces
	 * them. That function also passes a string key on as a named argument.
	 *
	 * Every output buffer opened above the level this method found is closed before it hands
	 * anything back, in the order it was opened, even when the function throws. A callback
	 * that left one open would otherwise leave the buffers of Twig out of step.
	 *
	 * @param string                  $function_name The name of the function.
	 * @param array<array-key, mixed> $arguments     Its arguments; one under a string key is a named argument.
	 * @throws \BadFunctionCallException When no function goes by that name.
	 * @return mixed
	 */
	private static function capture( string $function_name, array $arguments ): mixed {
		if ( ! is_callable( $function_name ) ) {
			throw new \BadFunctionCallException(
				sprintf(
					'The function "%s" does not exist. WordPress declares it once it has loaded, so render the templates that call it from a theme, a plugin or a mu-plugin.',
					$function_name
				)
			);
		}

		$level    = ob_get_level();
		$returned = null;
		$printed  = '';

		ob_start();

		try {
			$returned = call_user_func_array( $function_name, $arguments );
		} finally {
			while ( ob_get_level() > $level ) {
				$printed = (string) ob_get_clean() . $printed;
			}
		}

		if ( '' === $printed ) {
			return $returned;
		}

		if ( null === $returned || is_bool( $returned ) || $printed === $returned ) {
			return $printed;
		}

		echo $printed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- What the function printed, handed on as it printed it.

		return $returned;
	}
}
