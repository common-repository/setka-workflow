<?php
namespace Setka\Workflow\AJAX;

use Korobochkin\WPKit\Runners\AbstractRunner;

class AJAXRunner extends AbstractRunner
{
    /**
     * @inheritdoc
     */
    public static function run()
    {
        $ajax = self::getContainer()->get(AJAX::class);
        $ajax->register();
    }
}
