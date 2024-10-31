<?php
namespace Setka\Workflow\Admin\Services;

use Korobochkin\WPKit\Runners\AbstractRunner;

/**
 * Class AdminScriptStylesRunner
 */
class AdminScriptStylesRunner extends AbstractRunner
{
    /**
     * @inheritdoc
     */
    public static function run()
    {
        /**
         * @var $scripts AdminScriptStyles
         */
        $scripts = self::getContainer()->get(AdminScriptStyles::class);
        $scripts->register();
    }
}
