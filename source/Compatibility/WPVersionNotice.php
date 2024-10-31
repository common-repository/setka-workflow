<?php
namespace Setka\Workflow\Compatibility;

class WPVersionNotice
{
    /**
     * @var string Plugin base url.
     */
    protected $baseUrl;

    /**
     * @var string Plugin version.
     */
    protected $pluginVersion;

    /**
     * @var string Min WordPress version.
     */
    protected $wpVersionMin;

    public function run()
    {
        add_action('admin_init', array($this, 'init'));
    }

    public function init()
    {
        if (current_user_can('update_core') ||
            current_user_can('install_plugins') ||
            current_user_can('activate_plugins')
        ) {
            $this->addActions();
        }
    }

    public function addActions()
    {
        add_action('admin_notices', array($this, 'renderNotice'));
    }

    /**
     * Render notice.
     */
    public function renderNotice()
    {
        global $wp_version;
        ?>
        <div id="setka-workflow-notice-wp-min-version" class="notice setka-workflow-notice notice-error setka-workflow-notice-error">
            <p class="notice-title setka-workflow-notice-title"><?php esc_html_e('Setka Workflow plugin error', 'setka-workflow'); ?></p>
            <p><?php esc_html_e('Your WordPress version is obsolete. Please update your WordPress and then activate plugin again.', 'setka-workflow'); ?></p>
            <p><?php
                echo wp_kses(sprintf(
                    /* translators: %1$s - current WordPress version in X.Y.Z format. */
                    __('Your current WordPress version: <b>%1$s</b>', 'setka-workflow'),
                    esc_html($wp_version)
                ), array('b' => array()));
                echo '<br>';
                echo wp_kses(sprintf(
                    /* translators: %1$s - required WordPress version in X.Y.Z format. */
                    __('Minimal version for Setka Workflow plugin: <b>%1$s</b>', 'setka-workflow'),
                    esc_html($this->wpVersionMin)
                ), array('b' => array()));
                ?></p>
            <p><?php
                echo wp_kses(
                    __('Please contact Setka Workflow team at <a href="mailto:support@setka.io" target="_blank">support@setka.io</a>.', 'setka-workflow'),
                    array('a' => array('href' => array(), 'target' => array()))
                );
                ?></p>
        </div>
        <?php
    }

    /**
     * @param string $baseUrl
     *
     * @return $this For chain calls.
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @param string $pluginVersion
     *
     * @return $this For chain calls.
     */
    public function setPluginVersion($pluginVersion)
    {
        $this->pluginVersion = $pluginVersion;
        return $this;
    }

    /**
     * @param string $wpVersionMin
     *
     * @return $this For chain calls.
     */
    public function setWpVersionMin($wpVersionMin)
    {
        $this->wpVersionMin = $wpVersionMin;
        return $this;
    }
}
