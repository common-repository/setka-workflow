<?php
namespace Setka\Workflow\Options;

use Korobochkin\WPKit\Options\Special\BoolOption;
use Setka\Workflow\Plugin;

/**
 * Class ExportCategoriesAutomatically
 */
class ExportCategoriesAutomaticallyOption extends BoolOption
{
    /**
     * ExportCategoriesAutomatically constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->setName(Plugin::_NAME_ . '_export_categories_automatically')
            ->setDefaultValue(true);
    }
}
