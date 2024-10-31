<?php
namespace Setka\Workflow\Services;

use Korobochkin\WPKit\Runners\AbstractRunner;

class InstallRunner extends AbstractRunner
{
    /**
     * @inheritdoc
     */
    public static function run()
    {
        /**
         * @var $install Install
         */
        $install = self::getContainer()->get(Install::class);
        $install->run();
    }
}
