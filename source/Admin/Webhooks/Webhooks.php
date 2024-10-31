<?php
namespace Setka\Workflow\Admin\Webhooks;

use Setka\Workflow\Admin\Webhooks\Exceptions\ActionNotFoundException;
use Setka\Workflow\Admin\Webhooks\Exceptions\InvalidRequestBodyException;
use Setka\Workflow\Admin\Webhooks\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Class Webhooks
 */
class Webhooks
{
    /**
     * @var ActionInterface[]
     */
    protected $actions;

    /**
     * @var string Default action name.
     */
    protected $defaultActionName;

    /**
     * @var ActionInterface
     */
    protected $currentAction;

    /**
     * @var string Action name for WordPress.
     */
    protected $actionName;

    /**
     * @var Request HTTP request.
     */
    protected $request;

    /**
     * @var Response HTTP response.
     */
    protected $response;

    /**
     * Webhooks constructor.
     *
     * @param ActionInterface[] $actions
     * @param string $actionName Name used for attaching WordPress action.
     * @param string $defaultActionName Action name which should used if no name presented in request.
     */
    public function __construct(array $actions, $actionName = null, $defaultActionName = null)
    {
        $this->actionName        = $actionName;
        $this->actions           = $actions;
        $this->defaultActionName = $defaultActionName;

        if (empty($this->actions)) {
            throw new \InvalidArgumentException('Empty actions array.');
        }

        if (!isset($actions[$defaultActionName])) {
            throw new \InvalidArgumentException('Default action name not presented in actions list.');
        }
    }

    /**
     * @return $this For chain calls.
     */
    public function register()
    {
        if (empty($this->actions)) {
            throw new \LogicException('Specify actions before calling register');
        }

        if (empty($this->actionName)) {
            add_action('admin_post', array($this, 'handleRequest'));
            add_action('admin_post_nopriv', array($this, 'handleRequest'));
        } else {
            add_action('admin_post_' . $this->actionName, array($this, 'handleRequest'));
            add_action('admin_post_nopriv_' . $this->actionName, array($this, 'handleRequest'));
        }

        return $this;
    }

    public function handleRequest()
    {
        try {
            $this->response = new JsonResponse();
            $this->request  = Request::createFromGlobals();

            $this->requestManager();
        } catch (ActionNotFoundException $exception) {
            $this->response->setStatusCode(Response::HTTP_NOT_FOUND);
        } catch (UnauthorizedException $exception) {
            $this->response->setStatusCode(Response::HTTP_FORBIDDEN);
        } catch (InvalidRequestBodyException $exception) {
            $this->response->setStatusCode(Response::HTTP_BAD_REQUEST);
        } catch (\Exception $exception) {
            if ($this->response->getStatusCode() < 300) {
                // Error thrown but status code looks like successful.
                // Default status code for unknown errors.
                $this->response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } finally {
            $this->currentAction = null;
        }

        if ($this->response) {
            $this->send();
        }
    }

    protected function requestManager()
    {
        // Remove slashes (added by WordPress).
        if (isset($_POST)) {
            $post = $_POST;
            if (is_array($post)) {
                array_walk_recursive($post, function (&$value, $index) {
                    if (is_string($value)) {
                        $value = stripslashes($value);
                    }
                });
                $this->request->request = new ParameterBag($post);
            }
            unset($post);
        }

        $action = $this->request->request->get('actionName');
        if (!is_null($action) && isset($this->actions[$action])) {
            $this->currentAction = $this->actions[$action];
        } elseif ($this->defaultActionName) {
            $this->currentAction = $this->actions[$this->defaultActionName];
        } else {
            throw new ActionNotFoundException();
        }

        if (is_user_logged_in()) {
            if (!$this->currentAction->isEnabledForLoggedIn()) {
                throw new UnauthorizedException();
            }
        } else {
            if (!$this->currentAction->isEnabledForNotLoggedIn()) {
                throw new UnauthorizedException();
            }
        }

        $this->currentAction
            ->setViolations(new ConstraintViolationList())
            ->setRequest($this->request)
            ->setResponse($this->response)
            ->handleRequest();

        return $this;
    }

    /**
     * @see wp_send_json()
     */
    public function send()
    {
        $this->response->send();

        die;
    }
}
