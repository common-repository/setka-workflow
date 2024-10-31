<?php
namespace Setka\Workflow\Prototypes\MetaBoxes;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractMetaBox
 */
class AbstractMetaBox implements MetaBoxInterface
{
    /**
     * @var string Meta box ID (used in the 'id' attribute for the meta box).
     */
    protected $id;

    /**
     * @var string Title of the meta box.
     */
    protected $title;

    /**
     * @var MetaBoxViewInterface
     */
    protected $view;

    /**
     * @var string|string[] The screen or screens on which to show the box (such as a post type, 'link', or 'comment').
     */
    protected $screen;

    /**
     * @var string The context within the screen where the boxes should display.
     */
    protected $context;

    /**
     * @var string The priority within the context where the boxes should show ('high', 'low').
     */
    protected $priority = 'default';

    /**
     * @var FormFactoryInterface Form factory to building $form.
     */
    protected $formFactory;

    /**
     * @var FormInterface HTML Form.
     */
    protected $form;

    /**
     * @var object An object (instance) which holds the form data.
     */
    protected $formEntity;

    /**
     * @var Request Current HTTP request.
     */
    protected $request;

    /**
     * @inheritdoc
     */
    public function register()
    {
        add_meta_box(
            $this->getId(),
            $this->getTitle(),
            array($this, 'render'),
            $this->getScreen(),
            $this->getContext(),
            $this->getPriority()
        );

        add_action('load-post-new.php', array($this, 'lateConstruct'));
        add_action('load-post.php', array($this, 'lateConstruct'));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function lateConstruct()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @inheritdoc
     */
    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getScreen()
    {
        return $this->screen;
    }

    /**
     * @inheritdoc
     */
    public function setScreen($screen)
    {
        $this->screen = $screen;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @inheritdoc
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @inheritdoc
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @inheritdoc
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @inheritdoc
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFormEntity()
    {
        return $this->formEntity;
    }

    /**
     * @inheritdoc
     */
    public function setFormEntity($formEntity)
    {
        $this->formEntity = $formEntity;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function handleRequest()
    {
    }

    /**
     * @inheritdoc
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

    public function render()
    {
        $this->getView()->render($this);
    }
}
