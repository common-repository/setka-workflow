<?php
namespace Setka\Workflow;

use Korobochkin\WPKit\DataComponents\NodeFactory;
use Korobochkin\WPKit\Plugins\AbstractPlugin;
use Monolog\Logger;
use Setka\Workflow\Admin\MetaBoxes\MetaBoxes;
use Setka\Workflow\Admin\MetaBoxes\MetaBoxesRunner;
use Setka\Workflow\Admin\MetaBoxes\WorkflowID\WorkflowIDMetaBox;
use Setka\Workflow\Admin\Pages\AdminPages;
use Setka\Workflow\Admin\Pages\AdminPagesRunner;
use Setka\Workflow\Admin\Pages\FormFactory;
use Setka\Workflow\Admin\Pages\General\GeneralPage;
use Setka\Workflow\Admin\Pages\TwigFactory;
use Setka\Workflow\Admin\Services\AdminScriptStyles;
use Setka\Workflow\Admin\Services\AdminScriptStylesRunner;
use Setka\Workflow\Admin\Services\PluginActionLinks;
use Setka\Workflow\Admin\Services\PluginActionLinksRunner;
use Setka\Workflow\Admin\Services\SavePost;
use Setka\Workflow\Admin\Services\SavePostRunner;
use Setka\Workflow\Admin\Webhooks\Webhooks;
use Setka\Workflow\Admin\Webhooks\Actions\CreatePostAction;
use Setka\Workflow\Admin\Webhooks\WebhooksRunner;
use Setka\Workflow\AJAX\Actions\ExportCategoriesToWorkflow\ExportCategoriesToWorkflowAction;
use Setka\Workflow\AJAX\Actions\SearchUsers\SearchUsersAction;
use Setka\Workflow\AJAX\AJAX;
use Setka\Workflow\AJAX\AJAXRunner;
use Setka\Workflow\Options\AutoCreatedPostsAuthorIdOption;
use Setka\Workflow\Options\CurrentSpaceOption;
use Setka\Workflow\Options\ExportCategoriesAutomaticallyOption;
use Setka\Workflow\Options\PublishAutomaticallyOption;
use Setka\Workflow\Options\TokenOption;
use Setka\Workflow\Options\WorkflowPostTypesOption;
use Setka\Workflow\PostMeta\WorkflowTicketIdPostMeta;
use Setka\Workflow\Services\Account\Account;
use Setka\Workflow\Services\CreateTermsOnTheFly;
use Setka\Workflow\Services\CreateTermsOnTheFlyRunner;
use Setka\Workflow\Services\Install;
use Setka\Workflow\Services\InstallRunner;
use Setka\Workflow\Services\LoggerFactory;
use Setka\Workflow\Services\ScriptsDebug;
use Setka\Workflow\Services\SyncTerms\SyncTerms;
use Setka\Workflow\Services\TermMetaUtils;
use Setka\Workflow\Services\Translations;
use Setka\Workflow\Services\TranslationsRunner;
use Setka\Workflow\Services\Uninstall;
use Setka\Workflow\Services\UninstallRunner;
use Setka\Workflow\Services\WorkflowAPI\SyncTicket;
use Setka\Workflow\Services\WorkflowAPI\SyncTicketFactory;
use Setka\Workflow\Services\WorkflowAPI\WorkflowAPIFactory;
use Setka\Workflow\TermMeta\ExportedToWorkflowTermMeta;
use Setka\WorkflowSDK\Endpoints as WorkflowEndpoints;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

class Plugin extends AbstractPlugin
{
    const NAME = 'setka-workflow';

    const _NAME_ = 'setka_workflow';

    const VERSION = '2.0.0';

    /**
     * @inheritdoc
     */
    public function run()
    {
        InstallRunner::setContainer($this->container);
        register_activation_hook($this->getFile(), array(InstallRunner::class, 'run'));

        UninstallRunner::setContainer($this->container);
        register_uninstall_hook($this->getFile(), array(UninstallRunner::class, 'run'));

        TranslationsRunner::setContainer($this->container);
        add_action('plugins_loaded', array(TranslationsRunner::class, 'run'));

        CreateTermsOnTheFlyRunner::setContainer($this->container);
        add_action('created_term', array(CreateTermsOnTheFlyRunner::class, 'createdTerm'), 10, 3);

        SavePostRunner::setContainer($this->container);
        add_action('save_post', array(SavePostRunner::class, 'savePost'), 10, 3);

        return $this;
    }

    /**
     * Run plugin for WordPress admin area.
     *
     * @return $this For chain calls.
     */
    public function runAdmin()
    {
        AdminPagesRunner::setContainer($this->container);
        add_action('admin_menu', array(AdminPagesRunner::class, 'run'));

        AdminScriptStylesRunner::setContainer($this->container);
        add_action('admin_enqueue_scripts', array(AdminScriptStylesRunner::class, 'run'));

        AJAXRunner::setContainer($this->container);
        add_action('admin_init', array(AJAXRunner::class, 'run'));

        WebhooksRunner::setContainer($this->container);
        add_action('admin_init', array(WebhooksRunner::class, 'run'));

        MetaBoxesRunner::setContainer($this->container);
        add_action('admin_init', array(MetaBoxesRunner::class, 'run'));

        PluginActionLinksRunner::setContainer($this->container);
        add_filter('plugin_action_links_' . $this->getBasename(), array(PluginActionLinksRunner::class, 'actionLinks'));

        return $this;
    }

    /**
     * @return $this For chain calls.
     */
    public function configureDependencies()
    {
        /**
         * @var $container ContainerBuilder
         */
        $container = $this->getContainer();

        // Folder with cached files (templates + translations).
        $container->setParameter(
            'wp.plugins.setka_workflow.cache_dir',
            false
        );
        // Folder with Twig templates.
        $container->setParameter(
            'wp.plugins.setka_workflow.templates_path',
            path_join($this->getDir(), 'templates')
        );
        // Workflow API base uri.
        if (!$container->hasParameter('wp.plugins.setka_workflow.workflow_api_base_uri')) {
            if (defined('SETKA_WORKFLOW_API_BASE_URI')) {
                $container->setParameter(
                    'wp.plugins.setka_workflow.workflow_api_base_uri',
                    SETKA_WORKFLOW_API_BASE_URI
                );
            } else {
                $container->setParameter(
                    'wp.plugins.setka_workflow.workflow_api_base_uri',
                    WorkflowEndpoints::API
                );
            }
        }

        // Setka Workflow plugin Options.
        $container->setParameter(
            'wp.plugins.setka_workflow.all_options',
            array(
                new Reference(AutoCreatedPostsAuthorIdOption::class),
                new Reference(CurrentSpaceOption::class),
                new Reference(ExportCategoriesAutomaticallyOption::class),
                new Reference(PublishAutomaticallyOption::class),
                new Reference(TokenOption::class),
                new Reference(WorkflowPostTypesOption::class),
            )
        );

        // Setka Workflow plugin Term Metas.
        $container->setParameter(
            'wp.plugins.setka_workflow.all_term_metas',
            array(
                new Reference(ExportedToWorkflowTermMeta::class)
            )
        );

        $container
            ->register('wp.plugins.setka_workflow.logger.main', Logger::class)
            ->setFactory(array(LoggerFactory::class, 'create'))
            ->addArgument(self::_NAME_);

        // Factory for Twig.
        $container
            ->register(TwigFactory::class, TwigFactory::class)
            ->addArgument('%wp.plugins.setka_workflow.cache_dir%')
            ->addArgument('%wp.plugins.setka_workflow.templates_path%');

        // Twig itself prepared for rendering Symfony Forms.
        $container
            ->register('wp.plugins.setka_workflow.twig')
            ->setFactory(array(
                new Reference(TwigFactory::class),
                'create'
            ))
            ->setLazy(true);

        // Symfony Validator.
        $container
            ->register('wp.plugins.setka_workflow.validator')
            ->setFactory(array(Validation::class, 'createValidator'))
            ->setLazy(true);

        // Symfony Form Factory for factory %).
        $container
            ->register('wp.plugins.setka_workflow.form_factory_for_factory', FormFactory::class)
            ->addArgument(new Reference('wp.plugins.setka_workflow.validator'));

        // Symfony Form Factory.
        $container
            ->register('wp.plugins.setka_workflow.form_factory')
            ->setFactory(array(new Reference('wp.plugins.setka_workflow.form_factory_for_factory'), 'create'))
            ->setLazy(true);

        // Symfony Http Foundation Request.
        $container
            ->register('wp.plugins.setka_workflow.request')
            ->setFactory(array(Request::class, 'createFromGlobals'))
            ->setLazy(true);

        // Translations
        $container
            ->register(Translations::class, Translations::class)
            ->addArgument(self::NAME)
            ->addArgument(path_join(basename(dirname($this->getFile())), 'translations'));

        $container
            ->register('wp.plugins.setka_workflow.node_factory', NodeFactory::class)
            ->addArgument(new Reference('wp.plugins.setka_workflow.validator'));

        // Admin pages.
        $container
            ->register(AdminPages::class, AdminPages::class)
            ->addArgument(new Reference('wp.plugins.setka_workflow.twig'))
            ->addArgument(new Reference('wp.plugins.setka_workflow.form_factory'))
            ->addArgument(array(
                new Reference(GeneralPage::class),
            ));

        // General Page.
        $container
            ->register(GeneralPage::class, GeneralPage::class)
            ->addMethodCall('setAccount', array(new Reference(Account::class)))
            ->addMethodCall('setPublishAutomaticallyOption', array(new Reference(PublishAutomaticallyOption::class)))
            ->addMethodCall('setAutoCreatedPostsAuthorIdOption', array(new Reference(AutoCreatedPostsAuthorIdOption::class)))
            ->addMethodCall('setExportCategoriesAutomaticallyOption', array(new Reference(ExportCategoriesAutomaticallyOption::class)))
            ->addMethodCall('setAdminScriptStyles', array(new Reference(AdminScriptStyles::class)));

        $container
            ->register(PluginActionLinks::class, PluginActionLinks::class)
            ->addArgument(new Reference(Account::class))
            ->addArgument(new Reference(GeneralPage::class));

        $container
            ->register(SavePost::class, SavePost::class)
            ->addArgument(new Reference(SyncTicket::class))
            ->addArgument(new Reference(WorkflowPostTypesOption::class))
            ->addArgument(new Reference('wp.plugins.setka_workflow.logger.main'));

        // Options.
        // Token option (API license key).
        $container
            ->register(TokenOption::class, TokenOption::class)
            ->addMethodCall('setValidator', array(new Reference('wp.plugins.setka_workflow.validator')))
            ->setLazy(true);

        // Publish Automatically Option.
        $container
            ->register(PublishAutomaticallyOption::class, PublishAutomaticallyOption::class)
            ->addMethodCall('setValidator', array(new Reference('wp.plugins.setka_workflow.validator')))
            ->setLazy(true);

        // Current Space Option.
        $container
            ->register(CurrentSpaceOption::class, CurrentSpaceOption::class)
            ->addMethodCall('setValidator', array(new Reference('wp.plugins.setka_workflow.validator')))
            ->setLazy(true);

        $container
            ->register(ExportCategoriesAutomaticallyOption::class, ExportCategoriesAutomaticallyOption::class)
            ->addMethodCall('setValidator', array(new Reference('wp.plugins.setka_workflow.validator')))
            ->setLazy(true);

        // Workflow post types Option.
        $container
            ->register(WorkflowPostTypesOption::class, WorkflowPostTypesOption::class)
            ->addMethodCall('setValidator', array(new Reference('wp.plugins.setka_workflow.validator')))
            ->setLazy(true);

        // Auto created posts author id Option.
        $container
            ->register(AutoCreatedPostsAuthorIdOption::class, AutoCreatedPostsAuthorIdOption::class)
            ->addMethodCall('setValidator', array(new Reference('wp.plugins.setka_workflow.validator')))
            ->setLazy(true);

        // Term metas.
        $container
            ->register(ExportedToWorkflowTermMeta::class, ExportedToWorkflowTermMeta::class)
            ->addMethodCall('setValidator', array(new Reference('wp.plugins.setka_workflow.validator')))
            ->setLazy(true);

        $container
            ->register(Account::class, Account::class)
            ->addArgument(new Reference(TokenOption::class))
            ->addArgument(new Reference(CurrentSpaceOption::class))
            ->addArgument(new Reference('wp.plugins.setka_workflow.workflow_api'))
            ->addArgument(new Reference(TermMetaUtils::class))
            ->setLazy(true);

        // Term meta utils
        $container
            ->register(TermMetaUtils::class, TermMetaUtils::class)
            ->addArgument('%wp.plugins.setka_workflow.all_term_metas%');

        $container
            ->register(SyncTerms::class, SyncTerms::class)
            ->addArgument(new Reference(Account::class))
            ->addArgument(new Reference('wp.plugins.setka_workflow.workflow_api'));

        $container
            ->register(CreateTermsOnTheFly::class, CreateTermsOnTheFly::class)
            ->addArgument(new Reference(SyncTerms::class))
            ->addArgument(new Reference(ExportCategoriesAutomaticallyOption::class));

        // Script and styles for admin pages.
        $container
            ->register(AdminScriptStyles::class, AdminScriptStyles::class)
            ->addArgument($this->getUrl())
            ->addArgument(ScriptsDebug::isDebug());

        $container
            ->register(AJAX::class, AJAX::class)
            ->addArgument(array(
                ExportCategoriesToWorkflowAction::class => new Reference(ExportCategoriesToWorkflowAction::class),
                SearchUsersAction::class => new Reference(SearchUsersAction::class),
            ))
            ->addArgument(self::_NAME_);

        $container
            ->register(ExportCategoriesToWorkflowAction::class, ExportCategoriesToWorkflowAction::class)
            ->addMethodCall('setSyncTerms', array(new Reference(SyncTerms::class)))
            ->setLazy(true);

        $container
            ->register(SearchUsersAction::class, SearchUsersAction::class)
            ->setLazy(true);

        $container
            ->register('wp.plugins.setka_workflow.workflow_api')
            ->setFactory(array(WorkflowAPIFactory::class, 'create'))
            ->addArgument('%wp.plugins.setka_workflow.workflow_api_base_uri%')
            ->setLazy(true);

        $container
            ->register(SyncTicket::class, SyncTicket::class)
            ->setFactory(array(SyncTicketFactory::class, 'create'))
            ->addArgument(new Reference('wp.plugins.setka_workflow.node_factory'))
            ->addArgument(new Reference(PublishAutomaticallyOption::class))
            ->addArgument(new Reference(Account::class))
            ->addArgument(new Reference('wp.plugins.setka_workflow.workflow_api'));

        $container
            ->register(Install::class, Install::class)
            ->addArgument(new Reference(AutoCreatedPostsAuthorIdOption::class));

        $container
            ->register(Uninstall::class, Uninstall::class)
            ->addArgument('%wp.plugins.setka_workflow.all_options%')
            ->addArgument(null)
            ->addArgument('%wp.plugins.setka_workflow.all_term_metas%')
            ->addMethodCall('setTermMetaUtils', array(new Reference(TermMetaUtils::class)));

        $container
            ->register(Webhooks::class, Webhooks::class)
            ->addArgument(array(
                CreatePostAction::class => new Reference(CreatePostAction::class),
            ))
            ->addArgument(self::_NAME_)
            ->addArgument(CreatePostAction::class);

        $container
            ->register(CreatePostAction::class, CreatePostAction::class)
            ->addArgument(new Reference(Account::class))
            ->addArgument(new Reference(AutoCreatedPostsAuthorIdOption::class))
            ->addArgument(new Reference(WorkflowTicketIdPostMeta::class));

        // Meta Boxes.
        $container
            ->register(MetaBoxes::class, MetaBoxes::class)
            ->addArgument(new Reference('wp.plugins.setka_workflow.twig'))
            ->addArgument(new Reference('wp.plugins.setka_workflow.form_factory'))
            ->addArgument(array(
                new Reference(WorkflowIDMetaBox::class),
            ));

        // Workflow ID Meta Box.
        $container
            ->register(WorkflowIDMetaBox::class, WorkflowIDMetaBox::class)
            ->addArgument(new Reference(WorkflowPostTypesOption::class))
            ->addArgument('%wp.plugins.setka_workflow.workflow_api_base_uri%')

            ->addMethodCall('setAccount', array(new Reference(Account::class)))
            ->addMethodCall('setWorkflowTicketIdPostMeta', array(new Reference(WorkflowTicketIdPostMeta::class)))
            ->addMethodCall('setRequest', array(new Reference('wp.plugins.setka_workflow.request')));

        // Post Meta.
        // Workflow Ticket Id Post Meta.
        $container
            ->register(WorkflowTicketIdPostMeta::class, WorkflowTicketIdPostMeta::class)
            ->addMethodCall('setValidator', array(new Reference('wp.plugins.setka_workflow.validator')))
            ->setLazy(true);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return self::NAME;
    }
}
