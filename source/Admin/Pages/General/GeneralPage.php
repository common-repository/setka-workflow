<?php
namespace Setka\Workflow\Admin\Pages\General;

use Korobochkin\WPKit\Options\OptionInterface;
use Korobochkin\WPKit\Pages\SubMenuPage;
use Korobochkin\WPKit\Pages\Views\TwigPageView;
use Setka\Workflow\Admin\Services\AdminScriptStyles;
use Setka\Workflow\Plugin;
use Setka\Workflow\Services\Account\Account;
use Setka\Workflow\Services\Account\Exceptions\APIException;
use Setka\Workflow\Services\Account\Exceptions\InvalidResponseException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GeneralPage
 */
class GeneralPage extends SubMenuPage
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @var OptionInterface
     */
    protected $publishAutomaticallyOption;

    /**
     * @var OptionInterface
     */
    protected $autoCreatedPostsAuthorIdOption;

    /**
     * @var OptionInterface
     */
    protected $exportCategoriesAutomaticallyOption;

    /**
     * @var AdminScriptStyles
     */
    protected $adminScriptStyles;

    /**
     * GeneralPage constructor.
     */
    public function __construct()
    {
        $this->setParentSlug('options-general.php');
        $this->setPageTitle(__('Setka Workflow Integration', Plugin::NAME));
        $this->setMenuTitle(__('Setka Workflow', Plugin::NAME));
        $this->setCapability('manage_options');
        $this->setMenuSlug(Plugin::NAME);

        $this->setName('general_workflow');

        $pageView = new TwigPageView();
        $pageView->setTemplate('admin/pages/general/sign-in.html.twig');
        $this->setView($pageView);

        add_action('admin_enqueue_scripts', array($this, 'enqueueScriptStyles'));
    }

    /**
     * @inheritdoc
     */
    public function lateConstruct()
    {
        if ($this->getAccount()->isSignedIn()) {
            $entity = new Settings();
            $entity
                ->setToken($this->getAccount()->getTokenOption()->get())
                ->setPublishInWorkflow($this->getPublishAutomaticallyOption()->get())
                ->setExportCategoriesAutomatically($this->getExportCategoriesAutomaticallyOption()->get());

            if ($this->getAutoCreatedPostsAuthorIdOption()->get() != 0) {
                $entity->setPostAuthorId($this->getAutoCreatedPostsAuthorIdOption()->get());
            }

            $this->setFormEntity($entity);
            $this->setForm(
                $this->getFormFactory()->createNamed(
                    Plugin::_NAME_,
                    SettingsType::class,
                    $this->getFormEntity()
                )
            );

            $this->getView()->setTemplate('admin/pages/general/settings.html.twig');
        } else {
            $this->setFormEntity(new SignIn());
            $this->setForm(
                $this->getFormFactory()->createNamed(
                    Plugin::_NAME_,
                    SignInType::class,
                    $this->getFormEntity()
                )
            );
        }//end if

        $this->handleRequest();

        $attributes = array(
            'page' => $this,
            'form' => $this->getForm()->createView(),
            'translations' => array(
                'licenseKeyCaption' => sprintf(
                    __('Enter Setka Workflow API license key to start working. You can take it in your <a href="%1$s" target="_blank">Settings</a>.', Plugin::NAME),
                    'https://workflow.setka.io/'
                ),
                'licenseKeyFeatures' => __('Using the plugin you can:', Plugin::NAME),
                'licenseKeyFeaturesList' => array(
                    __('Export post categories from WordPress to Setka Workflow,', Plugin::NAME),
                    __('Sync ticket and post statuses,', Plugin::NAME),
                    __('Create a draft of a post in WordPress from your Setka Workflow Dashboard.', Plugin::NAME),
                ),

                'licenseKeyEnteredCaption' => __('You have successfully entered API License key.', Plugin::NAME),
                'publishInWorkflowCaption' => __('Mark Setka Workflow ticket as done when the post is published.', Plugin::NAME),
                'translations.postAuthorIdCaption' => __('Enable JavaScript to use this control.', Plugin::NAME),

                'exportCategoriesAutomatically' => __('Automatically export new created categories to Setka Workflow.', Plugin::NAME),

                'exportCategoriesFromWordPressToWorkflowLabel' => __('Export categories', Plugin::NAME),
                'exportCategoriesFromWordPressToWorkflowDescription' =>
                    __('By clicking this button you will start exporting WordPress Post categories to Workflow.', Plugin::NAME),
            ),
        );

        $this->getView()->setContext($attributes);
    }

    /**
     * @inheritdoc
     */
    public function handleRequest()
    {
        $this->setRequest(Request::createFromGlobals());
        $form = $this->getForm()->handleRequest($this->getRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $this->getFormEntity();

            if ($this->getAccount()->isSignedIn()) {
                if ($form->get(SettingsType::SIGN_OUT_BUTTON)->isClicked()) {
                    $this->getAccount()->signOut();
                } elseif ($form->get(SettingsType::EXPORT_CATEGORIES_FROM_WP_TO_WF_BUTTON)->isClicked()) {
                    // Do nothing since this functionality working via AJAX calls.
                } else {
                    /**
                     * @var $entity Settings
                     */
                    $this->getPublishAutomaticallyOption()->updateValue($entity->isPublishInWorkflow());
                    $this->getExportCategoriesAutomaticallyOption()->updateValue($entity->isExportCategoriesAutomatically());

                    if ($entity->getPostAuthorId()) {
                        $this->getAutoCreatedPostsAuthorIdOption()->updateValue($entity->getPostAuthorId());
                    } else {
                        $this->getAutoCreatedPostsAuthorIdOption()->delete();
                    }
                }
            } else {
                /**
                 * @var $entity SignIn
                 */
                try {
                    $this->getAccount()->signIn($entity->getToken());
                } catch (\Exception $exception) {
                    $exceptionReflection = new \ReflectionClass($exception);

                    switch (get_class($exception)) {
                        case APIException::class:
                        case InvalidResponseException::class:
                            $form->addError(new FormError(
                                sprintf(
                                    __('Oops... Your Setka Workflow license key is not valid. Please contact Setka Workflow support team workflow-help@setka.io. Error type: %1$s.', Plugin::NAME),
                                    $exceptionReflection->getShortName()
                                )
                            ));
                            break;

                        default:
                            $form->addError(new FormError(
                                sprintf(
                                    __('Error while during API request. Error type: %1$s.', Plugin::NAME),
                                    $exceptionReflection->getShortName()
                                )
                            ));
                            break;
                    }

                    return $this;
                }
            }

            wp_safe_redirect($this->getURL());
        }//end if
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Account $account
     *
     * @return $this
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @return OptionInterface
     */
    public function getPublishAutomaticallyOption()
    {
        return $this->publishAutomaticallyOption;
    }

    /**
     * @param OptionInterface $publishAutomaticallyOption
     *
     * @return $this
     */
    public function setPublishAutomaticallyOption(OptionInterface $publishAutomaticallyOption)
    {
        $this->publishAutomaticallyOption = $publishAutomaticallyOption;
        return $this;
    }

    /**
     * @return OptionInterface
     */
    public function getAutoCreatedPostsAuthorIdOption()
    {
        return $this->autoCreatedPostsAuthorIdOption;
    }

    /**
     * @param OptionInterface $autoCreatedPostsAuthorIdOption
     *
     * @return $this
     */
    public function setAutoCreatedPostsAuthorIdOption($autoCreatedPostsAuthorIdOption)
    {
        $this->autoCreatedPostsAuthorIdOption = $autoCreatedPostsAuthorIdOption;
        return $this;
    }

    /**
     * @return OptionInterface
     */
    public function getExportCategoriesAutomaticallyOption()
    {
        return $this->exportCategoriesAutomaticallyOption;
    }

    /**
     * @param OptionInterface $exportCategoriesAutomaticallyOption
     *
     * @return $this;
     */
    public function setExportCategoriesAutomaticallyOption($exportCategoriesAutomaticallyOption)
    {
        $this->exportCategoriesAutomaticallyOption = $exportCategoriesAutomaticallyOption;
        return $this;
    }

    /**
     * @return AdminScriptStyles
     */
    public function getAdminScriptStyles()
    {
        return $this->adminScriptStyles;
    }

    /**
     * @param AdminScriptStyles $adminScriptStyles
     *
     * @return $this
     */
    public function setAdminScriptStyles(AdminScriptStyles $adminScriptStyles)
    {
        $this->adminScriptStyles = $adminScriptStyles;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getURL()
    {
        // This method rewrite default since we need options-general.php instead of admin.php here.
        return add_query_arg(
            'page',
            $this->getMenuSlug(),
            admin_url('options-general.php')
        );
    }

    /**
     * @inheritdoc
     */
    public function enqueueScriptStyles()
    {
        wp_enqueue_style(Plugin::NAME . '-admin-settings-pages');
        wp_enqueue_style(Plugin::NAME . '-select2');
        wp_enqueue_script(Plugin::NAME . '-admin-general-page-initializer');
        $this->getAdminScriptStyles()->localizeGeneralPage();
    }
}
