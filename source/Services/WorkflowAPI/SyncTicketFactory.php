<?php
namespace Setka\Workflow\Services\WorkflowAPI;

use Korobochkin\WPKit\DataComponents\NodeFactory;
use Setka\Workflow\Options\PublishAutomaticallyOption;
use Setka\Workflow\PostMeta\WorkflowTicketIdPostMeta;
use Setka\Workflow\Services\Account\Account;
use Setka\WorkflowSDK\API;

class SyncTicketFactory
{
    public static function create(
        NodeFactory $nodeFactory,
        PublishAutomaticallyOption $publishAutomaticallyOption,
        Account $account,
        API $api
    ) {
        /**
         * @var $meta WorkflowTicketIdPostMeta
         */
        $meta = $nodeFactory->create(WorkflowTicketIdPostMeta::class);
        return new SyncTicket(
            $meta,
            $publishAutomaticallyOption,
            $account,
            $api
        );
    }
}
