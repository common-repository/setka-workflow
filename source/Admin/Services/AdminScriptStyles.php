<?php
namespace Setka\Workflow\Admin\Services;

use Setka\Workflow\AJAX\Actions\ExportCategoriesToWorkflow\ExportCategoriesToWorkflowAction;
use Setka\Workflow\AJAX\Actions\SearchUsers\SearchUsersAction;
use Setka\Workflow\Plugin;

/**
 * Class AdminScriptStyles
 */
class AdminScriptStyles
{
    /**
     * @var string Url to folder with plugin.
     */
    protected $pluginUrl;

    /**
     * @var boolean Minified files for dev.
     */
    protected $dev;

    /**
     * ScriptStyles constructor.
     *
     * @param $pluginUrl string Url to folder with plugin.
     * @param $dev bool Not min files for dev.
     */
    public function __construct($pluginUrl, $dev)
    {
        $this->pluginUrl = $pluginUrl;
    }

    /**
     * Register scripts and styles in WordPress API.
     */
    public function register()
    {
        if ($this->dev) {
            $file = 'assets/js/admin/general-page/general-page.js';
        } else {
            $file = 'assets/js/admin/general-page/general-page.min.js';
        }
        wp_register_script(
            Plugin::NAME . '-admin-general-page',
            path_join($this->pluginUrl, $file),
            array('backbone', 'jquery', 'jquery-ui-core', 'jquery-ui-progressbar', Plugin::NAME . '-select2'),
            Plugin::VERSION . '1',
            true
        );

        wp_register_script(
            Plugin::NAME . '-admin-general-page-initializer',
            path_join($this->pluginUrl, 'assets/js/admin/general-page-initializer/general-page-initializer.js'),
            array(Plugin::NAME . '-admin-general-page'),
            Plugin::VERSION,
            true
        );

        if ($this->dev) {
            $file = 'assets/css/admin/settings-pages/settings-pages.css';
        } else {
            $file = 'assets/css/admin/settings-pages/settings-pages.min.css';
        }
        wp_register_style(
            Plugin::NAME . '-admin-settings-pages',
            path_join($this->pluginUrl, $file),
            array(),
            Plugin::VERSION
        );

        if ($this->dev) {
            $file = 'assets/js/select2/js/select2.js';
        } else {
            $file = 'assets/js/select2/js/select2.min.js';
        }
        wp_register_script(
            Plugin::NAME . '-select2',
            path_join($this->pluginUrl, $file),
            array('jquery'),
            Plugin::VERSION,
            true
        );
        if ($this->dev) {
            $file = 'assets/js/select2/css/select2.css';
        } else {
            $file = 'assets/js/select2/css/select2.min.css';
        }
        wp_register_style(
            Plugin::NAME . '-select2',
            path_join($this->pluginUrl, $file),
            array(),
            Plugin::VERSION
        );
    }

    public function localizeGeneralPage()
    {
        wp_localize_script(
            Plugin::NAME . '-admin-general-page',
            'setkaWorkflowAdminGeneralPageConfig',
            array(
                'action' => Plugin::_NAME_,
                'actionExportCategories' => ExportCategoriesToWorkflowAction::class,
                'actionSearchUsers' => SearchUsersAction::class,
                'elements' => array(
                    'exportButton' => '#setka_workflow_exportCategoriesFromWordPressToWorkflow',

                    'exportErrors' => '#setka-workflow-export-errors',
                    'exportLog' => '#setka-workflow-export-log',
                    'exportProgress' => '#setka-workflow-export-progress',

                    'hidden' => 'setka-workflow-hidden',

                    'postAuthorId' => '#setka_workflow_postAuthorId',
                ),
                'translations' => array(
                    'connectToWordPressError' => __('Oops...  Your WordPress server is not available now.', Plugin::NAME),
                    'exportedResult' => __('Category created: %1$s', Plugin::NAME),
                    'exportSuccessfulFinished' => __('All categories have been exported.', Plugin::NAME),
                ),
            )
        );
    }
}
