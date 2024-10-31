<?php
namespace Setka\Workflow\Admin\Services;

use Korobochkin\WPKit\Pages\SubMenuPageInterface;
use Setka\Workflow\Plugin;
use Setka\Workflow\Services\Account\Account;

/**
 * Class PluginActionLinks
 */
class PluginActionLinks
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @var SubMenuPageInterface
     */
    protected $pluginSettingsPage;

    /**
     * PluginActionLinks constructor.
     *
     * @param Account $account
     * @param SubMenuPageInterface $pluginSettingsPage
     */
    public function __construct(Account $account, SubMenuPageInterface $pluginSettingsPage)
    {
        $this->account            = $account;
        $this->pluginSettingsPage = $pluginSettingsPage;
    }

    /**
     * Adds plugin action links (along with Deactivate | Edit | Delete).
     *
     * @param $links array Default links created by WordPress.
     *
     * @return array Default links + our custom links.
     */
    public function actionLinks(array $links)
    {
        if ($this->account->isSignedIn()) {
            $additional = array(
                'settings' => sprintf(
                    '<a href="%1$s">%2$s</a>',
                    esc_url($this->pluginSettingsPage->getURL()),
                    esc_html_x('Settings', 'Plugin action link', Plugin::NAME)
                ),
            );

            $links = $additional + $links;
        } else {
            $additional = array(
                'start' => sprintf(
                    '<a href="%1$s">%2$s</a>',
                    esc_url($this->pluginSettingsPage->getURL()),
                    esc_html_x('Start', 'Plugin action link', Plugin::NAME)
                ),
            );

            $links = $additional + $links;
        }

        return $links;
    }
}
