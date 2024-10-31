<?php
namespace Setka\Workflow\Services\WorkflowAPI;

use Setka\Workflow\Options\PublishAutomaticallyOption;
use Setka\Workflow\PostMeta\WorkflowTicketIdPostMeta;
use Setka\Workflow\Services\Account\Account;
use Setka\Workflow\Services\PostUtilities;
use Setka\WorkflowSDK\Actions\Tickets\PublishTicketAction;
use Setka\WorkflowSDK\Actions\Tickets\UnpublishTicketAction;
use Setka\WorkflowSDK\Actions\Tickets\UpdateTicketAction;
use Setka\WorkflowSDK\API;
use Setka\WorkflowSDK\Entities\TicketEntity;

class SyncTicket
{
    const PUBLISH = 'publish';

    const UN_PUBLISH = 'un-publish';

    /**
     * @var string 'publish' or 'un-publish'
     */
    protected $currentAction;

    /**
     * @var \WP_Post
     */
    protected $post;

    /**
     * @var WorkflowTicketIdPostMeta
     */
    protected $workflowTicketIdPostMeta;

    /**
     * @var PublishAutomaticallyOption
     */
    protected $publishAutomaticallyOption;

    /**
     * @var Account
     */
    protected $account;

    /**
     * @var API
     */
    protected $api;

    /**
     * SyncTicket constructor.
     *
     * @param WorkflowTicketIdPostMeta $workflowTicketIdPostMeta
     * @param PublishAutomaticallyOption $publishAutomaticallyOption
     * @param Account $account
     * @param API $api
     */
    public function __construct(
        WorkflowTicketIdPostMeta $workflowTicketIdPostMeta,
        PublishAutomaticallyOption $publishAutomaticallyOption,
        Account $account,
        API $api
    ) {
        $this->workflowTicketIdPostMeta   = $workflowTicketIdPostMeta;
        $this->publishAutomaticallyOption = $publishAutomaticallyOption;
        $this->account                    = $account;
        $this->api                        = $api;

        $this->api->getAuth()->setToken($this->account->getTokenOption()->get());
    }

    /**
     * Update ticket details includes it status.
     *
     * @throws \Exception
     * @return TicketEntity
     */
    public function publish()
    {
        $this->currentAction = self::PUBLISH;
        return $this->handlePost();
    }

    /**
     * Update ticket details includes it status.
     *
     * @throws \Exception
     * @return TicketEntity
     */
    public function unPublish()
    {
        $this->currentAction = self::UN_PUBLISH;
        return $this->handlePost();
    }

    /**
     * Performs updates on ticket.
     *
     * @throws \Exception In case if any errors.
     * @return TicketEntity If both operations was successful then Entity returns.
     */
    protected function handlePost()
    {
        try {
            $ticket = $this->updateTicket();

            if ($this->getPublishAutomaticallyOption()->get()) {
                $ticket = $this->updateTicketStatus();
            }

            return $ticket;
        } finally {
            $this->currentAction = null;
        }
    }

    /**
     * Update ticket title and links.
     *
     * @throws \Exception In case if any errors.
     *
     * @return TicketEntity If request was successful then Entity returns.
     */
    protected function updateTicket()
    {
        $updateAction = new UpdateTicketAction($this->getApi());

        if (self::PUBLISH === $this->currentAction) {
            $viewUrl = PostUtilities::getViewUrl($this->getPost());
        } else {
            $viewUrl = PostUtilities::getViewGuidUrl($this->getPost());
        }

        $updateAction->setDetails($updateAction->configureDetails(
            array(
                'space' => $this->getAccount()->getSpaceShortName(),
                'id' => $this->getWorkflowTicketIdPostMeta()->setPostId($this->getPost()->ID)->get(),
                'options' => array(
                    'json' => array(
                        'title' => $this->getPost()->post_title,
                        'edit_post_url' => PostUtilities::getEditUrl($this->getPost()->ID),
                        'view_post_url' => $viewUrl,
                    ),
                ),
            )
        ));

        return $updateAction->request()->handleResponse();
    }

    /**
     * Update ticket status.
     *
     * @throws \Exception In case if any errors.
     *
     * @return TicketEntity If request was successful then Entity returns.
     */
    protected function updateTicketStatus()
    {
        if (self::PUBLISH == $this->currentAction) {
            $ticketAction = new PublishTicketAction($this->getApi());
        } else {
            $ticketAction = new UnpublishTicketAction($this->getApi());
        }

        $ticketAction->setDetails($ticketAction->configureDetails(
            array(
                'space' => $this->getAccount()->getSpaceShortName(),
                'id' => $this->getWorkflowTicketIdPostMeta()->setPostId($this->getPost()->ID)->get(),
                'options' => array('json' => array())
            )
        ));

        return $ticketAction->request()->handleResponse();
    }

    /**
     * @return \WP_Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param \WP_Post $post
     * @return $this For chain calls.
     */
    public function setPost(\WP_Post $post)
    {
        $this->post = $post;
        return $this;
    }

    /**
     * @return WorkflowTicketIdPostMeta
     */
    public function getWorkflowTicketIdPostMeta()
    {
        return $this->workflowTicketIdPostMeta;
    }

    /**
     * @param WorkflowTicketIdPostMeta $workflowTicketIdPostMeta
     * @return $this For chain calls.
     */
    public function setWorkflowTicketIdPostMeta(WorkflowTicketIdPostMeta $workflowTicketIdPostMeta)
    {
        $this->workflowTicketIdPostMeta = $workflowTicketIdPostMeta;
        return $this;
    }

    /**
     * @return PublishAutomaticallyOption
     */
    public function getPublishAutomaticallyOption()
    {
        return $this->publishAutomaticallyOption;
    }

    /**
     * @param PublishAutomaticallyOption $publishAutomaticallyOption
     * @return $this For chain calls.
     */
    public function setPublishAutomaticallyOption(PublishAutomaticallyOption $publishAutomaticallyOption)
    {
        $this->publishAutomaticallyOption = $publishAutomaticallyOption;
        return $this;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Account $account
     * @return $this For chain calls.
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @return API
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param API $api
     * @return $this For chain calls.
     */
    public function setApi(API $api)
    {
        $this->api = $api;
        return $this;
    }
}
