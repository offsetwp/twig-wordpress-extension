<?php
/**
 * A fixture project of the OffsetWP Twig WordPress Extension test suite.
 *
 * The one line the README tells a project to write, in the file a project writes it in.
 *
 * @author Jérôme Wohlschlegel
 * @package OffsetWP\Twig\Extension\WordPressExtension\Tests\Fixtures
 */

declare( strict_types=1 );

use OffsetWP\Bundle\TwigBundle\Configuration\TwigConfig;
use OffsetWP\Twig\Extension\WordPressExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function ( ContainerConfigurator $container ): void {
	TwigConfig::create()
		->extension( WordPressExtension::class )
		->apply( $container );
};
