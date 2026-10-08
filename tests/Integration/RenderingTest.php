<?php
/**
 * OffsetWP Twig WordPress Extension Tests
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension\WordPressExtension\Tests\Integration
 */

declare( strict_types=1 );

namespace OffsetWP\Twig\Extension\WordPressExtension\Tests\Integration;

use OffsetWP\Twig\Extension\WordPressExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\ArrayLoader;

/**
 * Templates rendered by a real environment, with HTML autoescaping on as Twig ships it.
 *
 * Every assertion here is about a template, written the way a theme writes one: a function
 * of WordPress gives back what WordPress gives, wherever the template puts the call, and
 * whether the environment yields or not.
 */
#[CoversClass( WordPressExtension::class )]
final class RenderingTest extends TestCase {

	/**
	 * What a function returns is printed as WordPress returns it, not escaped a second time.
	 *
	 * @return void
	 */
	public function testAFunctionThatReturnsIsPrintedAsWordPressReturnsIt(): void {
		$this->assertSame( '<h1>Fish &amp; <em>Chips</em> #7</h1>', $this->render( '<h1>{{ get_the_title(7) }}</h1>' ) );
	}

	/**
	 * __() hands the translation over as WordPress does.
	 *
	 * @return void
	 */
	public function testATranslationIsPrintedAsWordPressReturnsIt(): void {
		$this->assertSame( 'A & B', $this->render( "{{ __('A & B', 'offsetwp') }}" ) );
	}

	/**
	 * What is not a function is escaped as the environment says: this extension marks its
	 * own functions, and nothing else.
	 *
	 * @return void
	 */
	public function testAVariableIsStillEscapedByTheEnvironment(): void {
		$this->assertSame( 'A &amp; B', $this->render( '{{ title }}', array( 'title' => 'A & B' ) ) );
	}

	/**
	 * A function that prints is printed where it is called.
	 *
	 * @return void
	 */
	public function testAFunctionThatPrintsIsPrintedWhereItIsCalled(): void {
		$this->assertSame(
			'<a href="https://example.test/?p=7">link</a>',
			$this->render( '<a href="{{ the_permalink(7) }}">link</a>' )
		);
	}

	/**
	 * What a function prints can be kept in a variable, and printed later.
	 *
	 * @return void
	 */
	public function testWhatAFunctionPrintsCanBeSet(): void {
		$this->assertSame( 'before [https://example.test/?p=7]', $this->render( '{% set link = the_permalink(7) %}before [{{ link }}]' ) );
	}

	/**
	 * Markup kept in a variable is a variable from then on: the environment escapes it when it
	 * is printed, unless the template says raw, as the README tells it to.
	 *
	 * @return void
	 */
	public function testMarkupKeptInAVariableIsEscapedAsAVariable(): void {
		$this->assertSame(
			'&lt;aside class=&quot;widget&quot;&gt;Hello&lt;/aside&gt;|<aside class="widget">Hello</aside>',
			$this->render( "{% set widgets = dynamic_sidebar('footer') %}{{ widgets }}|{{ widgets|raw }}" )
		);
	}

	/**
	 * What a function prints goes through a filter.
	 *
	 * @return void
	 */
	public function testWhatAFunctionPrintsGoesThroughAFilter(): void {
		$this->assertSame( 'HTTPS://EXAMPLE.TEST/?P=7', $this->render( '{{ the_permalink(7)|upper }}' ) );
	}

	/**
	 * A function that prints nothing, and returns false, is false to a condition.
	 *
	 * @return void
	 */
	public function testAFunctionThatPrintsNothingIsFalseToACondition(): void {
		$this->assertSame(
			'no menu',
			$this->render( "{% if wp_nav_menu({theme_location: 'footer'}) %}menu{% else %}no menu{% endif %}" )
		);
	}

	/**
	 * A function that prints its markup and returns it as well is printed once.
	 *
	 * @return void
	 */
	public function testAFunctionThatPrintsAndReturnsTheSameMarkupIsPrintedOnce(): void {
		$this->assertSame( '<nav class="post-pages">1 2</nav>', $this->render( '{{ wp_link_pages() }}' ) );
	}

	/**
	 * A function that prints its markup and returns a boolean gives its markup, and nothing
	 * when it had nothing to print.
	 *
	 * @return void
	 */
	public function testAFunctionThatPrintsAndReturnsABooleanGivesItsMarkup(): void {
		$this->assertSame(
			'<aside class="widget">Hello</aside>|',
			$this->render( "{{ dynamic_sidebar('footer') }}|{{ dynamic_sidebar('missing') }}" )
		);
	}

	/**
	 * A function told not to print gives back what it returns.
	 *
	 * @return void
	 */
	public function testAFunctionToldNotToPrintGivesBackWhatItReturns(): void {
		$this->assertSame(
			'<nav class="menu"><a href="/">Home</a></nav>|<h1>Fish &amp; <em>Chips</em></h1>',
			$this->render( "{{ wp_nav_menu({echo: false}) }}|{{ the_title('<h1>', '</h1>', false) }}" )
		);
	}

	/**
	 * A function that returns takes its named arguments as WordPress names them, and the
	 * ones left out keep their defaults.
	 *
	 * @return void
	 */
	public function testNamedArgumentsReachAFunctionThatReturns(): void {
		$this->assertSame(
			'<img src="/7-post-thumbnail.jpg" class="wide">',
			$this->render( "{{ get_the_post_thumbnail(7, attr='wide') }}" )
		);
	}

	/**
	 * A function that prints takes its named arguments as well, after positional ones or
	 * alone.
	 *
	 * @return void
	 */
	public function testNamedArgumentsReachAFunctionThatPrints(): void {
		$this->assertSame(
			'Fish &amp; <em>Chips</em>!|<h2>Fish &amp; <em>Chips</em>',
			$this->render( "{{ the_title(after='!') }}|{{ the_title('<h2>', display=true) }}" )
		);
	}

	/**
	 * An environment that yields renders a template whose functions print in the order the
	 * template calls them, with nothing escaping to the output on the way: the place where an
	 * echo left alone would have gone wrong.
	 *
	 * @return void
	 */
	public function testTheOrderHoldsWhenTheEnvironmentYields(): void {
		$this->assertSame(
			'<head><meta name="generator" content="WordPress"></head><main>HTTPS://EXAMPLE.TEST/?P=7</main>',
			$this->render(
				'<head>{{ wp_head() }}</head><main>{% apply upper %}{{ the_permalink(7) }}{% endapply %}</main>',
				array(),
				array( 'use_yield' => true )
			)
		);
	}

	/**
	 * The header and the footer a theme keeps in header.php and footer.php are printed where
	 * the template calls get_header() and get_footer(), in an environment that yields: called
	 * directly, what the files print would land outside what the template renders.
	 *
	 * @return void
	 */
	public function testTheHeaderAndTheFooterOfTheThemeArePrintedWhereTheTemplateCallsThem(): void {
		$this->assertSame(
			'<header>Fish &amp; Chips</header><main>Menu</main><footer>Fish &amp; Chips, since 1860</footer>',
			$this->render( '{{ get_header() }}<main>Menu</main>{{ get_footer() }}', array(), array( 'use_yield' => true ) )
		);
	}

	/**
	 * A specialized header is asked for by its name, in order or as WordPress names the
	 * argument, and a name the theme has no template for falls back to header.php, as in
	 * WordPress.
	 *
	 * @return void
	 */
	public function testASpecializedHeaderIsAskedForByItsName(): void {
		$this->assertSame(
			'<header class="shop">Shop</header>|<header class="shop">Shop</header>|<header>Fish &amp; Chips</header>',
			$this->render( "{{ get_header('shop') }}|{{ get_header(name='shop') }}|{{ get_header('blog') }}" )
		);
	}

	/**
	 * A function no catalogue lists is called through fn(), with named arguments, and its result is
	 * printed as it is given.
	 *
	 * @return void
	 */
	public function testFnCallsAFunctionOfAPlugin(): void {
		$this->assertSame(
			'Hi, Ada & co!|Fish &amp; <em>Chips</em> #7',
			$this->render( "{{ fn('offsetwp_test_greet', 'Ada & co', greeting='Hi') }}|{{ fn('get_the_title', 7) }}" )
		);
	}

	/**
	 * A notice a getter prints on the way to its value goes to the output where the template
	 * called the getter, and the value is what the template reads.
	 *
	 * @return void
	 */
	public function testANoticeOfAGetterIsPrintedWhereItWasCalled(): void {
		$this->assertSame(
			'[Deprecated: offsetwp_test_terms() is deprecated.News;Events;]',
			$this->render( "[{% for term in fn('offsetwp_test_terms') %}{{ term }};{% endfor %}]" )
		);
	}

	/**
	 * What fn() refuses is named, and Twig names the template and the line.
	 *
	 * @return void
	 */
	public function testFnRefusesAFunctionOfPhp(): void {
		$this->expectException( Error::class );
		$this->expectExceptionMessage( '"exec" is a function of PHP itself' );

		$this->render( "{{ fn('exec', 'ls') }}" );
	}

	/**
	 * A function of the catalogue that prints, called where WordPress has not declared it,
	 * says what is missing.
	 *
	 * @return void
	 */
	public function testAMissingFunctionThatPrintsSaysWhatIsMissing(): void {
		$this->expectException( Error::class );
		$this->expectExceptionMessage( 'The function "wp_footer" does not exist. WordPress declares it once it has loaded' );

		$this->render( '{{ wp_footer() }}' );
	}

	/**
	 * A function of the catalogue that returns, and that WordPress has not declared, says the
	 * same: the template compiles, and the call names what is missing.
	 *
	 * @return void
	 */
	public function testAMissingFunctionThatReturnsSaysWhatIsMissing(): void {
		$this->expectException( Error::class );
		$this->expectExceptionMessage( 'The function "get_bloginfo" does not exist. WordPress declares it once it has loaded' );

		$this->render( "{{ get_bloginfo('name') }}" );
	}

	/**
	 * Render one template with the extension, in an environment configured as Twig ships it
	 * unless a test says otherwise.
	 *
	 * @param string               $template The source of the template.
	 * @param array<string, mixed> $context  The variables it reads.
	 * @param array<string, mixed> $options  Options of the environment.
	 * @return string
	 */
	private function render( string $template, array $context = array(), array $options = array() ): string {
		$twig = new Environment( new ArrayLoader( array( 'page.twig' => $template ) ), $options + array( 'strict_variables' => true ) );

		$twig->addExtension( new WordPressExtension() );

		return $twig->render( 'page.twig', $context );
	}
}
