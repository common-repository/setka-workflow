<?php
namespace Setka\Workflow\Admin\Webhooks;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Interface ActionInterface
 */
interface ActionInterface
{
    /**
     * @return bool
     */
    public function isEnabledForLoggedIn();

    /**
     * @param $enabledForLoggedIn bool
     *
     * @return $this
     */
    public function setEnabledForLoggedIn($enabledForLoggedIn);

    /**
     * @return bool
     */
    public function isEnabledForNotLoggedIn();

    /**
     * @param $enabledForNotLoggedIn bool
     *
     * @return $this
     */
    public function setEnabledForNotLoggedIn($enabledForNotLoggedIn);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $name string
     *
     * @return $this
     */
    public function setName($name);

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations();

    /**
     * @param ConstraintViolationListInterface $violations
     *
     * @return $this
     */
    public function setViolations(ConstraintViolationListInterface $violations);

    /**
     * @return Request
     */
    public function getRequest();

    /**
     * @param Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request);

    /**
     * @return Response
     */
    public function getResponse();

    /**
     * @param Response $response
     *
     * @return $this
     */
    public function setResponse(Response $response);

    /**
     * @return $this
     */
    public function handleRequest();
}
