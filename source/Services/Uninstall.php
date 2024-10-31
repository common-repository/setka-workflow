<?php
namespace Setka\Workflow\Services;

use Korobochkin\WPKit\Cron\CronSingleEventInterface;
use Korobochkin\WPKit\Options\OptionInterface;
use Korobochkin\WPKit\PostMeta\PostMetaInterface;
use Korobochkin\WPKit\TermMeta\TermMetaInterface;
use Korobochkin\WPKit\Transients\TransientInterface;
use Korobochkin\WPKit\Utils\WordPressFeatures;

/**
 * Class Uninstall
 */
class Uninstall
{
    /**
     * @var OptionInterface[] Options created by the plugin.
     */
    protected $options;

    /**
     * @var PostMetaInterface[] Post Metas created by the plugin.
     */
    protected $postMetas;

    /**
     * @var TermMetaInterface[] Term Metas created by the plugin.
     */
    protected $termMetas;

    /**
     * @var TransientInterface[] Transients created by the plugin.
     */
    protected $transients;

    /**
     * @var CronSingleEventInterface[] Cron events created by the plugin.
     */
    protected $cronEvents;

    /**
     * @var TermMetaUtils Utility to manage Term Metas.
     */
    protected $termMetaUtils;

    /**
     * Uninstall constructor.
     *
     * @param $options OptionInterface[] Options created by the plugin.
     * @param $postMetas PostMetaInterface[] Post Metas created by the plugin.
     * @param $termMetas TermMetaInterface[] Term Metas created by the plugin.
     * @param $transients TransientInterface[] Transients created by the plugin.
     * @param $cronEvents CronSingleEventInterface[] Cron events created by the plugin.
     */
    public function __construct(
        $options = array(),
        $postMetas = array(),
        $termMetas = array(),
        $transients = array(),
        $cronEvents = array()
    ) {
        $this->options    = $options;
        $this->postMetas  = $postMetas;
        $this->termMetas  = $termMetas;
        $this->transients = $transients;
        $this->cronEvents = $cronEvents;
    }

    /**
     * Run the un-installer.
     *
     * @return $this For chain calls.
     */
    public function run()
    {
        $this->deleteOptions();

        try {
            $this->termMetaUtils->deleteTermMetas();
        } catch (\Exception $exception) {
            // Do nothing.
        }

        wp_cache_flush();

        return $this;
    }

    /**
     * Delete all options from WordPress.
     *
     * @return $this For chain calls.
     */
    public function deleteOptions()
    {
        foreach ($this->options as $option) {
            $option->delete();
        }

        return $this;
    }

    /**
     * @return TermMetaUtils
     */
    public function getTermMetaUtils()
    {
        return $this->termMetaUtils;
    }

    /**
     * @param TermMetaUtils $termMetaUtils
     *
     * @return $this For chain calls.
     */
    public function setTermMetaUtils(TermMetaUtils $termMetaUtils)
    {
        $this->termMetaUtils = $termMetaUtils;
        return $this;
    }
}
