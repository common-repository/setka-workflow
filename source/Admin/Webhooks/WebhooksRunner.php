<?php
namespace Setka\Workflow\Admin\Webhooks;

use Korobochkin\WPKit\Runners\AbstractRunner;

/**
 * Class WebhooksRunner
 */
class WebhooksRunner extends AbstractRunner
{
    /**
     * @inheritdoc
     */
    public static function run()
    {
        /**
         * @var $webhooks Webhooks
         */
        $webhooks = self::getContainer()->get(Webhooks::class);
        $webhooks->register();
    }
}
