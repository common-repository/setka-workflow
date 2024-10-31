<?php
namespace Setka\Workflow\AJAX;

use Symfony\Component\HttpFoundation\JsonResponse;
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
}
