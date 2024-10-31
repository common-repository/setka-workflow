<?php
namespace Setka\Workflow\Admin\MetaBoxes;

use Korobochkin\WPKit\Runners\AbstractRunner;

/**
 * Class MetaBoxesRunner
 */
class MetaBoxesRunner extends AbstractRunner
{
    /**
     * @inheritdoc
     */
    public static function run()
    {
        /**
         * @var $metaBoxes MetaBoxes
         */
        $metaBoxes = self::getContainer()->get(MetaBoxes::class);
        $metaBoxes->register();
    }
}
