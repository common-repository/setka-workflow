<?php
namespace Setka\Workflow\Admin\MetaBoxes\WorkflowID;

use Korobochkin\WPKit\Options\OptionInterface;
use Korobochkin\WPKit\PostMeta\PostMetaInterface;
use Setka\Workflow\Plugin;
use Setka\Workflow\Prototypes\MetaBoxes\AbstractMetaBox;
use Setka\Workflow\Prototypes\MetaBoxes\TwigMetaBoxView;
use Setka\Workflow\Services\Account\Account;
use Setka\Workflow\Services\WorkflowTicketUtilities;
use Setka\WorkflowSDK\API;

class WorkflowIDMetaBox extends AbstractMetaBox
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @var OptionInterface
     */
    protected $workflowPostTypesOption;

    /**
     * @var string Base Url to Setka API.
     */
    protected $baseUrl;

    /**
     * @var API
     */
    protected $api;

    /**
     * @var PostMetaInterface
     */
    protected $workflowTicketIdPostMeta;

    /**
     * WorkflowIDMetaBox constructor.
     *
     * @param $workflowPostTypesOption OptionInterface
     * @param $baseUrl string Base url to Setka API.
     */
    public function __construct(
        OptionInterface $workflowPostTypesOption,
        $baseUrl
    ) {
        $this
            ->setWorkflowPostTypesOption($workflowPostTypesOption)
            ->setBaseUrl($baseUrl)

            ->setId(Plugin::_NAME_ . '_workflow_id')
            ->setTitle(_x('Setka Workflow', 'MetaBox title.', Plugin::NAME))
            ->setScreen($this->getWorkflowPostTypesOption()->get())
            ->setContext('side');

        $view = new TwigMetaBoxView();
        $view->setTemplate('admin/meta-boxes/workflow-id/workflow-id.html.twig');
        $this->setView($view);
    }

    /**
     * @inheritdoc
     */
    public function lateConstruct()
    {
        $this->setFormEntity(new WorkflowId());

        if ($this->getRequest()->query->has('post')) {
            $postId = (int) $this->getRequest()->query->get('post');
        } elseif ($this->getRequest()->request->has('post_ID')) {
            $postId = (int) $this->getRequest()->request->get('post_ID');
        }

        if (isset($postId)) {
            $ticketId = (int) $this->getWorkflowTicketIdPostMeta()->setPostId($postId)->get();

            if ($ticketId > 0) {
                $ticketUtils = new WorkflowTicketUtilities($this->getBaseUrl(), $this->getAccount());

                $ticketUtils->setTicketId($ticketId)->generateUrl();
                $this->getFormEntity()->setUrl($ticketUtils->getUrlGenerated());
            }
        }

        $this->setForm(
            $this->getFormFactory()->createNamed(
                Plugin::_NAME_,
                WorkflowIdType::class,
                $this->getFormEntity()
            )
        );

        $this->handleRequest();

        $attributes = array(
            'metaBox' => $this,
            'form' => $this->getForm()->createView(),
            'translations' => array(
                'urlCaption' => __('Enter Setka Workflow Ticket Url', Plugin::NAME),
            ),
        );

        $this->getView()->setContext($attributes);

        return $this;
    }

    public function handleRequest()
    {
        // Handle form submission only if user allowed to edit current post.
        if (!current_user_can('edit_post', $this->getWorkflowTicketIdPostMeta()->getPostId())) {
            return $this;
        }

        $form = $this->getForm()->handleRequest($this->getRequest());

        if ($form->isSubmitted() && $this->getWorkflowTicketIdPostMeta()->getPostId()) {
            if ($this->getFormEntity()->getUrl()) {
                try {
                    $ticketUtils = new WorkflowTicketUtilities($this->getBaseUrl(), $this->getAccount());
                    $ticketId    = $ticketUtils
                        ->setUrl($this->getFormEntity()->getUrl())
                        ->parseUrl()
                        ->getTicketId();
                    $this->getWorkflowTicketIdPostMeta()->updateValue($ticketId);
                } catch (\Exception $exception) {
                    // Do nothing.
                }
            } else {
                $this->getWorkflowTicketIdPostMeta()->delete();
            }
        }
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
     *
     * @return $this
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @return OptionInterface
     */
    public function getWorkflowPostTypesOption()
    {
        return $this->workflowPostTypesOption;
    }

    /**
     * @param OptionInterface $workflowPostTypesOption
     *
     * @return $this
     */
    public function setWorkflowPostTypesOption(OptionInterface $workflowPostTypesOption)
    {
        $this->workflowPostTypesOption = $workflowPostTypesOption;
        return $this;
    }

    /**
     * @return PostMetaInterface
     */
    public function getWorkflowTicketIdPostMeta()
    {
        return $this->workflowTicketIdPostMeta;
    }

    /**
     * @param PostMetaInterface $workflowTicketIdPostMeta
     *
     * @return $this
     */
    public function setWorkflowTicketIdPostMeta($workflowTicketIdPostMeta)
    {
        $this->workflowTicketIdPostMeta = $workflowTicketIdPostMeta;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }
}
