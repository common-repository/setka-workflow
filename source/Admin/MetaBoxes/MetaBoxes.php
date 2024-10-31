<?php
namespace Setka\Workflow\Admin\MetaBoxes;

use Setka\Workflow\Prototypes\MetaBoxes\MetaBoxInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class MetaBoxes
 */
class MetaBoxes
{
    /**
     * @var MetaBoxInterface[]
     */
    protected $metaBoxes = array();

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * AdminPages constructor.
     *
     * @param $twig \Twig_Environment
     * @param $formFactory FormFactoryInterface
     * @param $metaBoxes MetaBoxInterface[]
     */
    public function __construct(\Twig_Environment $twig, FormFactoryInterface $formFactory, $metaBoxes)
    {
        $this->twig        = $twig;
        $this->formFactory = $formFactory;
        $this->metaBoxes   = $metaBoxes;
        $this->initializeMetaBoxes();
    }

    protected function initializeMetaBoxes()
    {
        $metaBoxesAssoc = array();
        foreach ($this->metaBoxes as $metaBox) {
            $metaBoxesAssoc[$metaBox->getId()] = $metaBox;
            $metaBox->setFormFactory($this->formFactory);
            $metaBox->getView()->setTwigEnvironment($this->twig);
        }
        $this->metaBoxes = $metaBoxesAssoc;
    }

    public function register()
    {
        foreach ($this->metaBoxes as $metaBox) {
            $metaBox->register();
        }
    }
}
