<?php
namespace Setka\Workflow\Options;

use Korobochkin\WPKit\Options\Special\BoolOption;
use Setka\Workflow\Plugin;

/**
 * Class PublishAutomaticallyOption
 */
class PublishAutomaticallyOption extends BoolOption
{
    /**
     * PublishAutomaticallyOption constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->setName(Plugin::_NAME_ . '_publish_automatically')
            ->setDefaultValue(true);
    }
}
