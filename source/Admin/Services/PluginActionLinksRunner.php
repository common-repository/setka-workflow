<?php
namespace Setka\Workflow\Admin\Services;

use Korobochkin\WPKit\Runners\AbstractRunner;

/**
 * Class PluginActionLinksRunner
 */
class PluginActionLinksRunner extends AbstractRunner
{
    /**
     * @inheritdoc
     */
    public static function run()
    {
    }

    /**
     * Adds plugin action links (along with Deactivate | Edit | Delete).
     *
     * @param $links array Default links created by WordPress.
     *
     * @return array Default links + our custom links.
     */
    public static function actionLinks(array $links)
    {
        /**
         * @var $pluginActionLinks PluginActionLinks
         */
        $pluginActionLinks = self::getContainer()->get(PluginActionLinks::class);
        return $pluginActionLinks->actionLinks($links);
    }
}
