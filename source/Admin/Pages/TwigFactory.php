<?php
namespace Setka\Workflow\Admin\Pages;

use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormRenderer;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;

class TwigFactory
{
    /**
     * @var string|false Path to folder with cache files or false if cache disabled.
     */
    protected $cache = false;

    /**
     * @var string Path to folder with Twig templates.
     */
    protected $templatesPath;

    /**
     * TwigFactory constructor.
     *
     * @param false|string $cache
     * @param string $templatesPath
     */
    public function __construct($cache, $templatesPath)
    {
        $this->cache         = $cache;
        $this->templatesPath = $templatesPath;
    }

    /**
     * Creates \Twig\Environment instance.
     *
     * @throws \ReflectionException
     *
     * @return \Twig\Environment
     */
    public function create()
    {
        $cacheTwig = ($this->cache) ? $this->cache . 'twig/' : false;

        $reflection = new \ReflectionClass(HttpFoundationExtension::class);

        $twig = new Environment(
            new FilesystemLoader(
                array(
                    $this->templatesPath,
                    dirname(dirname($reflection->getFileName())) . '/Resources/views/Form',
                )
            ),
            array(
                'cache' => $cacheTwig,
            )
        );

        $formEngine = new TwigRendererEngine(array('form_div_layout.html.twig'), $twig);
        $twig->addRuntimeLoader(
            new FactoryRuntimeLoader(
                array(
                    FormRenderer::class => function () use ($formEngine) {
                        return new FormRenderer($formEngine);
                    },
                )
            )
        );

        $twig->addExtension(new FormExtension());
        $twig->addExtension(new TranslationExtension());

        return $twig;
    }
}
