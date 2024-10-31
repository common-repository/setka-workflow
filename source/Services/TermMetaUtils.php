<?php
namespace Setka\Workflow\Services;

use Korobochkin\WPKit\TermMeta\TermMetaInterface;
use Korobochkin\WPKit\Utils\WordPressFeatures;

/**
 * Class TermMetaUtils
 */
class TermMetaUtils
{
    /**
     * @var TermMetaInterface[]
     */
    protected $termMetas;

    /**
     * TermMetaUtils constructor.
     *
     * @param TermMetaInterface[] $termMetas
     */
    public function __construct(array $termMetas)
    {
        $this->termMetas = $termMetas;
    }

    /**
     * Removes all terms meta.
     *
     * Be sure to call this method only if WordPress Term Meta supported.
     *
     * @throws \Exception If WordPress not support Term Metas.
     *
     * @return $this For chain calls.
     */
    public function deleteTermMetas()
    {
        if (!WordPressFeatures::isTermsMetaSupported()) {
            throw new \Exception('Your WordPress is not supported Term Metas');
        }

        /**
         * @var $wpdb \wpdb
         */
        global $wpdb;

        $queryTemplate = "
            DELETE
              
            FROM {$wpdb->termmeta}
            
            WHERE meta_key = %s
            ";

        foreach ($this->termMetas as $termMeta) {
            $query = $wpdb->prepare(
                $queryTemplate,
                $termMeta->getName()
            );
            $wpdb->get_results($query);
        }

        return $this;
    }

    /**
     * Reset all WordPress cache.
     *
     * @return $this For chain calls.
     */
    public function resetCache()
    {
        wp_cache_flush();
        return $this;
    }
}
