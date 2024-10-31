<?php
namespace Setka\Workflow\Prototypes\MetaBoxes;

/**
 * Interface MetaBoxViewInterface
 */
interface MetaBoxViewInterface
{
    /**
     * Output HTML markup for the page.
     *
     * @param MetaBoxInterface $metaBox Meta box instance.
     */
    public function render(MetaBoxInterface $metaBox);

    /**
     * @return string
     */
    public function getTemplate();

    /**
     * @param string $template
     *
     * @return $this
     */
    public function setTemplate($template);

    /**
     * @return array
     */
    public function getContext();

    /**
     * @param array $context Content for Twig
     *
     * @return $this
     */
    public function setContext(array $context);

    /**
     * @return \Twig_Environment
     */
    public function getTwigEnvironment();

    /**
     * @param \Twig_Environment $twigEnvironment
     *
     * @return $this
     */
    public function setTwigEnvironment($twigEnvironment);
}
