<?php
use Setka\Workflow\Plugin;
use Setka\Workflow\Compatibility\Compatibility;
use Setka\Workflow\Compatibility\PHPVersionNotice;
use Setka\Workflow\Compatibility\WPVersionNotice;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/*
    Plugin Name: Setka Workflow
    Plugin URI: https://workflow.setka.io/
    Description: Setka Workflow Integration Plugin is designed to integrate your WordPress site with Setka Workflow space.
    Author: Native Grid LLC
    Author URI: https://setka.io/
    Version: 2.0.0
    Text Domain: setka-workflow
    Domain Path: /languages/
    License: GPLv2 or later
*/

function setkaWorkflowRunner()
{
    $compatibility   = true;
    $pluginVersion   = '2.0.0';
    $phpVersionMin   = '7.1.3';
    $phpVersionIDMin = 70130;
    $wpVersionMin    = '4.0';

    // Check for minimum PHP version
    require_once __DIR__ . '/source/Compatibility/Compatibility.php';
    if (!Compatibility::checkPHP($phpVersionIDMin)) {
        require_once __DIR__ . '/source/Compatibility/PHPVersionNotice.php';
        $PHPVersionNotice = new PHPVersionNotice();
        $PHPVersionNotice
            ->setBaseUrl(plugin_dir_url(__FILE__))
            ->setPluginVersion($pluginVersion)
            ->setPhpVersionMin($phpVersionMin)
            ->run();
        $compatibility = false;
    }

    // Check for minimum WordPress version
    if (!Compatibility::checkWordPress($wpVersionMin)) {
        require_once __DIR__ . '/source/Compatibility/WPVersionNotice.php';
        $WPVersionNotice = new WPVersionNotice();
        $WPVersionNotice
            ->setBaseUrl(plugin_dir_url(__FILE__))
            ->setPluginVersion($pluginVersion)
            ->setWpVersionMin($wpVersionMin)
            ->run();
        $compatibility = false;
    }

    if ($compatibility) {
        global $container;

        if (!class_exists('Setka\Workflow\Plugin')) {
            // If class not exists this means what a wordpress.org version running
            // and we need require our own autoloader.
            // If you using WordPress installation with composer just require
            // your own autoload.php as usual. In this case plugin don't require any
            // additional autoloaders.
            require_once __DIR__ . '/vendor/autoload.php';
        }

        $plugin = $GLOBALS['WPSetkaWorkflowPlugin'] = new Plugin(__FILE__);

        if (isset($container) && is_a($container, ContainerBuilder::class)) {
            $plugin->setContainer($container);
        } else {
            $plugin->setContainer(new ContainerBuilder());
        }

        $plugin->configureDependencies()->run();

        if (is_admin()) {
            $plugin->runAdmin();
        }
    }
}
setkaWorkflowRunner();
