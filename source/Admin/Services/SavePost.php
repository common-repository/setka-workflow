<?php
namespace Setka\Workflow\Admin\Services;

use Psr\Log\LoggerInterface;
use Setka\Workflow\Options\WorkflowPostTypesOption;
use Setka\Workflow\Services\WorkflowAPI\SyncTicket;

class SavePost
{
    /**
     * @var SyncTicket
     */
    protected $syncTicket;

    /**
     * @var WorkflowPostTypesOption
     */
    protected $workflowPostTypesOption;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SavePost constructor.
     *
     * @param SyncTicket $syncTicket
     * @param WorkflowPostTypesOption $workflowPostTypesOption
     * @param LoggerInterface $logger
     */
    public function __construct(
        SyncTicket $syncTicket,
        WorkflowPostTypesOption $workflowPostTypesOption,
        LoggerInterface $logger
    ) {
        $this->syncTicket              = $syncTicket;
        $this->workflowPostTypesOption = $workflowPostTypesOption;
        $this->logger                  = $logger;
    }

    /**
     * @param int $id Post ID.
     * @param \WP_Post $post Post object.
     * @param bool $update Whether this is an existing post being updated or not.
     *
     * @return $this For chain calls.
     */
    public function handlePost($id, \WP_Post $post, $update)
    {
        try {
            $postTypes = $this->workflowPostTypesOption->get();
            if (!in_array($post->post_type, $postTypes, true)) {
                return $this;
            }

            $this->syncTicket->setPost($post);
            if ('publish' === $post->post_status) {
                $this->syncTicket->publish();
            } else {
                $this->syncTicket->unPublish();
            }

            return $this;
        } catch (\Exception $exception) {
            $this->logger->critical(
                sprintf(
                    'Exception thrown while updating ticket details. %s: "%s" at %s line %s',
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine()
                ),
                array('exception' => $exception)
            );
        }
    }
}
