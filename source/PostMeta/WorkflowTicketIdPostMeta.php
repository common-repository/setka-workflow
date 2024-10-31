<?php
namespace Setka\Workflow\PostMeta;

use Korobochkin\WPKit\PostMeta\Special\NumericPostMeta;
use Setka\Workflow\Plugin;

/**
 * Class WorkflowTicketIdPostMeta
 */
class WorkflowTicketIdPostMeta extends NumericPostMeta
{
    /**
     * WorkflowTicketIdPostMeta constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this
            ->setName(Plugin::_NAME_ . '_workflow_ticket_id')
            ->setVisibility(false);
    }
}
