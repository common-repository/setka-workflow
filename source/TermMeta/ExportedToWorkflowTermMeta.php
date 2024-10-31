<?php
namespace Setka\Workflow\TermMeta;

use Korobochkin\WPKit\TermMeta\Special\BoolTermMeta;
use Setka\Workflow\Plugin;

class ExportedToWorkflowTermMeta extends BoolTermMeta
{
    /**
     * ExportedToWorkflowTermMeta constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this
            ->setName(Plugin::_NAME_ . '_exported_to_workflow')
            ->setDefaultValue(false);
    }
}
