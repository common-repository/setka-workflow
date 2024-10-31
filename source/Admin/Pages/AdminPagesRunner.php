<?php
namespace Setka\Workflow\Admin\Pages;

use Korobochkin\WPKit\Runners\AbstractRunner;

/**
 * Class AdminPagesRunner
 */
class AdminPagesRunner extends AbstractRunner
{
    /**
     * @inheritdoc
     */
    public static function run()
    {
        $pages = self::getContainer()->get(AdminPages::class);
        $pages->register();
    }
}
