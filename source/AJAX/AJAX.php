<?php
namespace Setka\Workflow\AJAX;

use Setka\Workflow\AJAX\Exceptions\ActionNotFoundException;
use Setka\Workflow\AJAX\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;

class AJAX
{
    /**
     * @var ActionInterface[]
     */
    protected $actions;

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
     * AJAX constructor.
     *
     * @param ActionInterface[] $actions
     * @param string $actionName Name used for attaching WordPress action.
     */
    public function __construct(array $actions, $actionName)
    {
        $this->actionName = $actionName;
        $this->actions    = $actions;

        if (is_null($this->actions)) {
            throw new \InvalidArgumentException('Invalid actions array.');
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

        add_action('wp_ajax_'        . $this->actionName, array($this, 'handleRequest'));
        add_action('wp_ajax_nopriv_' . $this->actionName, array($this, 'handleRequest'));

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
                foreach ($post as &$fragment) {
                    $fragment = stripslashes($fragment);
                }
                $this->request->request = new ParameterBag($post);
            }
            unset($post, $fragment);
        }

        $action = $this->request->request->get('actionName');
        if (is_null($action)) {
            $action = $this->request->query->get('actionName');
            $action = stripslashes($action);
        }
        if (!is_null($action) && isset($this->actions[$action])) {
            if (is_string($this->actions[$action])) {
                $this->actions[$action] = new $this->actions[$action]();
            }

            $this->currentAction = $this->actions[$action];

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
        } else {
            throw new ActionNotFoundException();
        }
    }

    /**
     * @see wp_send_json()
     */
    public function send()
    {
        $this->response->send();

        // This part grabbed from wp_send_json()
        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_die();
        } else {
            die;
        }
    }
}
