<?php
namespace Setka\Workflow\Services;

use Korobochkin\WPKit\Options\OptionInterface;
use Setka\Workflow\Services\SyncTerms\SyncTerms;

/**
 * Class CreateTermsOnTheFly
 */
class CreateTermsOnTheFly
{
    /**
     * @var SyncTerms
     */
    protected $syncTerms;

    /**
     * @var OptionInterface
     */
    protected $exportCategoriesAutomatically;

    /**
     * CreateTermsOnTheFly constructor.
     *
     * @param $syncTerms SyncTerms
     * @param $exportCategoriesAutomatically OptionInterface
     */
    public function __construct(SyncTerms $syncTerms, OptionInterface $exportCategoriesAutomatically)
    {
        $this->syncTerms                     = $syncTerms;
        $this->exportCategoriesAutomatically = $exportCategoriesAutomatically;
    }

    /**
     * @param $termId int Term ID.
     * @param $taxonomyId int Term taxonomy ID.
     * @param $taxonomy string Taxonomy name.
     *
     * @return $this For chain calls.
     */
    public function run($termId, $taxonomyId, $taxonomy)
    {
        if (!$this->exportCategoriesAutomatically->get()) {
            return $this;
        }

        if ($taxonomy !== 'category') {
            return $this;
        }

        try {
            $workflowCategoryEntity = $this->syncTerms
                ->setTaxonomy($taxonomy)
                ->exportTermToWorkflow($termId);
        } catch (\Exception $exception) {
            // Do nothing.
        }

        return $this;
    }
}
