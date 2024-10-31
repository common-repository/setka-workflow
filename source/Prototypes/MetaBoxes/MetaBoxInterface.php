<?php
namespace Setka\Workflow\Prototypes\MetaBoxes;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

interface MetaBoxInterface
{
    /**
     * @return $this For chain calls.
     */
    public function register();

    /**
     * Prepares the meta box for rendering.
     *
     * This method called from $this->render().
     *
     * @return $this For chain calls.
     */
    public function lateConstruct();

    /**
     * @return string
     */
    public function getId();

    /**
     * @param string $id
     *
     * @return $this For chain calls.
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     *
     * @return $this For chain calls.
     */
    public function setTitle($title);

    /**
     * @return mixed
     */
    public function getView();

    /**
     * @param mixed $view
     *
     * @return $this For chain calls.
     */
    public function setView($view);

    /**
     * @return string|string[]
     */
    public function getScreen();

    /**
     * @param string|string[] $screen
     *
     * @return $this For chain calls.
     */
    public function setScreen($screen);

    /**
     * @return string
     */
    public function getContext();

    /**
     * @param string $context
     *
     * @return $this For chain calls.
     */
    public function setContext($context);

    /**
     * @return string
     */
    public function getPriority();

    /**
     * @param string $priority
     *
     * @return $this For chain calls.
     */
    public function setPriority($priority);

    /**
     * Returns form factory.
     *
     * @return FormFactoryInterface Form factory for building forms.
     */
    public function getFormFactory();

    /**
     * Sets the form factory to build forms.
     *
     * @param $formFactory FormFactoryInterface Form factory.
     *
     * @return $this For chain calls.
     */
    public function setFormFactory(FormFactoryInterface $formFactory);

    /**
     * Returns the form for this page.
     *
     * @return FormInterface HTML form.
     */
    public function getForm();

    /**
     * Sets the form for this page.
     *
     * @param FormInterface $form HTML form.
     *
     * @return $this For chain calls.
     */
    public function setForm(FormInterface $form);

    /**
     * Returns the form data entity.
     *
     * @return object Form entity.
     */
    public function getFormEntity();

    /**
     * Sets the form entity.
     *
     * @param object $formEntity form data-entity.
     *
     * @return $this For chain calls.
     */
    public function setFormEntity($formEntity);

    /**
     * Be sure to call it only from $this->lateConstruct()
     * to prevent illegal access to the page handling.
     */
    public function handleRequest();

    /**
     * Returns HTTP request.
     *
     * @return Request HTTP Request.
     */
    public function getRequest();

    /**
     * Sets HTTP request.
     *
     * @param Request $request HTTP request.
     *
     * @return $this For chain calls.
     */
    public function setRequest(Request $request);

    /**
     * Render the meta box with PageView instance.
     *
     * This method outputting HTML.
     */
    public function render();
}
