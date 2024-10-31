<?php
namespace Setka\Workflow\Services;

use Korobochkin\WPKit\Runners\AbstractRunner;

class UninstallRunner extends AbstractRunner
{
    /**
     * @inheritdoc
     */
    public static function run()
    {
        /**
         * @var $uninstall Uninstall
         */
        $uninstall = self::getContainer()->get(Uninstall::class);

        $uninstall->run();
    }
}
