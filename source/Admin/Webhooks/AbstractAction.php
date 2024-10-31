<?php
namespace Setka\Workflow\Admin\Webhooks;

use Setka\Workflow\Admin\Webhooks\Exceptions\InvalidRequestBodyException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractAction implements ActionInterface
{
    /**
     * @var $enabledForLoggedIn bool
     */
    protected $enabledForLoggedIn = false;

    /**
     * @var $enabledForNotLoggedIn bool
     */
    protected $enabledForNotLoggedIn = false;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var $violations ConstraintViolationListInterface
     */
    protected $violations;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ParameterBag
     */
    protected $requestContent;

    /**
     * @var JsonResponse
     */
    protected $response;

    /**
     * @inheritdoc
     */
    public function isEnabledForLoggedIn()
    {
        return $this->enabledForLoggedIn;
    }

    /**
     * @inheritdoc
     */
    public function setEnabledForLoggedIn($enabledForLoggedIn)
    {
        $this->enabledForLoggedIn = $enabledForLoggedIn;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEnabledForNotLoggedIn()
    {
        return $this->enabledForNotLoggedIn;
    }

    /**
     * @inheritdoc
     */
    public function setEnabledForNotLoggedIn($enabledForNotLoggedIn)
    {
        $this->enabledForNotLoggedIn = $enabledForNotLoggedIn;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @inheritdoc
     */
    public function setViolations(ConstraintViolationListInterface $violations)
    {
        $this->violations = $violations;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @inheritdoc
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return ParameterBag
     */
    public function getRequestContent()
    {
        return $this->requestContent;
    }

    /**
     * @param ParameterBag $requestContent
     *
     * @return $this For chain calls.
     */
    public function setRequestContent(ParameterBag $requestContent)
    {
        $this->requestContent = $requestContent;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @inheritdoc
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * This function transform Request content from JSON string into Parameter bag.
     *
     * Transforms only if content-type header have application/json value.
     *
     * @throws InvalidRequestBodyException If PHP cant parse JSON from request.
     *
     * @return $this For chain calls.
     */
    protected function convertRequestContent()
    {
        if ($this->getRequest()->getContentType() == 'json') {
            $content = json_decode($this->getRequest()->getContent(), true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($content)) {
                $this->setRequestContent(new ParameterBag($content));
            } else {
                throw new InvalidRequestBodyException();
            }
        } else {
            $this->setRequestContent(new ParameterBag());
        }

        return $this;
    }
}
