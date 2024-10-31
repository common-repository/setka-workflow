<?php
namespace Setka\Workflow\Admin\Webhooks\Actions;

use Korobochkin\WPKit\Options\OptionInterface;
use Korobochkin\WPKit\PostMeta\PostMetaInterface;
use Setka\Workflow\Admin\Webhooks\AbstractAction;
use Setka\Workflow\Admin\Webhooks\Exceptions\InvalidRequestBodyException;
use Setka\Workflow\Admin\Webhooks\Exceptions\UnauthorizedException;
use Setka\Workflow\Services\Account\Account;
use Setka\Workflow\Services\PostUtilities;
use Symfony\Component\HttpFoundation\Response;

class CreatePostAction extends AbstractAction
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @var OptionInterface
     */
    protected $autoCreatedPostsAuthorIdOption;

    /**
     * @var PostMetaInterface
     */
    protected $workflowTicketIdPostMeta;

    /**
     * CreatePostAction constructor.
     *
     * @param $account Account
     * @param $autoCreatedPostsAuthorIdOption OptionInterface
     * @param $workflowTicketIdPostMeta PostMetaInterface
     */
    public function __construct(
        Account $account,
        OptionInterface $autoCreatedPostsAuthorIdOption,
        PostMetaInterface $workflowTicketIdPostMeta
    ) {
        $this
            ->setName(self::class)
            ->setEnabledForNotLoggedIn(true);

        $this->account                        = $account;
        $this->autoCreatedPostsAuthorIdOption = $autoCreatedPostsAuthorIdOption;
        $this->workflowTicketIdPostMeta       = $workflowTicketIdPostMeta;
    }

    /**
     * @inheritdoc
     */
    public function handleRequest()
    {
        $response = $this->getResponse();

        $this->convertRequestContent();

        if (!$this->account->compareToken($this->getRequestContent()->get('token'))) {
            throw new UnauthorizedException();
        }

        $postFromRequest = $this->getRequestContent()->get('post');

        if (!isset($postFromRequest['ticket_id']) || !is_int($postFromRequest['ticket_id'])) {
            throw new InvalidRequestBodyException();
        }

        if (!isset($postFromRequest['title'])) {
            throw new InvalidRequestBodyException();
        }

        $authorId = (int) $this->autoCreatedPostsAuthorIdOption->get();
        if ($authorId <= 0) {
            throw new \Exception();
        }

        $result = wp_insert_post(array(
            'post_author' => $authorId,
            'post_title' => $this->getRequestContent()->get('post')['title'],
        ));

        if (is_int($result) && $result > 0) {
            $this->workflowTicketIdPostMeta
                ->setPostId($result)
                ->updateValue($postFromRequest['ticket_id']);

            $responseData = array(
                'edit_post_url' => PostUtilities::getEditUrl($result),
                'view_post_url' => PostUtilities::getViewGuidUrl($result),
            );

            $response
                ->setData($responseData)
                ->setStatusCode(Response::HTTP_OK);
        } else {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return $this;
    }
}
