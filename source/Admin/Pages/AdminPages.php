<?php
namespace Setka\Workflow\Admin\Pages;

use Korobochkin\WPKit\Pages\PageInterface;
use Korobochkin\WPKit\Pages\Views\PageViewInterface;
use Korobochkin\WPKit\Pages\Views\TwigPageView;
use Setka\Workflow\Admin\Pages\General\GeneralPage;
use Setka\Workflow\Services\Account\Account;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class AdminPages
 */
class AdminPages
{
    /**
     * @var PageInterface[]
     */
    protected $pages = array();

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var PageViewInterface
     */
    protected $pageView;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * AdminPages constructor.
     *
     * @param $twig \Twig_Environment
     * @param $formFactory FormFactoryInterface
     * @param $pages array
     */
    public function __construct(\Twig_Environment $twig, FormFactoryInterface $formFactory, $pages)
    {
        $this->twig        = $twig;
        $this->formFactory = $formFactory;
        $this->pages       = $pages;
        $this->initializePages();
    }

    protected function initializePages()
    {
        $pagesAssoc = array();
        foreach ($this->pages as $page) {
            $pagesAssoc[$page->getName()] = $page;
            $page->setFormFactory($this->formFactory);
            $page->getView()->setTwigEnvironment($this->twig);
        }
        $this->pages = $pagesAssoc;
    }

    public function register()
    {
        foreach ($this->pages as $page) {
            $page->register();
        }
    }
}
