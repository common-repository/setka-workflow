<?php
namespace Setka\Workflow\Services\SyncTerms;

use Korobochkin\WPKit\TermMeta\TermMetaInterface;
use Korobochkin\WPKit\Utils\Compatibility;
use Korobochkin\WPKit\Utils\WordPressFeatures;
use Setka\Workflow\Services\Account\Account;
use Setka\Workflow\Services\SyncTerms\Exceptions\APIErrorException;
use Setka\Workflow\Services\SyncTerms\Exceptions\OutdatedWordPressException;
use Setka\Workflow\Services\SyncTerms\Exceptions\TermsNotFoundException;
use Setka\Workflow\TermMeta\ExportedToWorkflowTermMeta;
use Setka\WorkflowSDK\Actions\Categories\CreateCategoryAction;
use Setka\WorkflowSDK\API;
use Setka\WorkflowSDK\APIFactory;
use Setka\WorkflowSDK\Entities\CategoryEntity;

/**
 * Class SyncCategories
 */
class SyncTerms
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @var API
     */
    protected $api;

    /**
     * @var CreateCategoryAction
     */
    protected $createCategoryAction;

    /**
     * @var string The name of taxonomy ("category", "tags").
     */
    protected $taxonomy;

    /**
     * @var TermMetaInterface
     */
    protected $termMeta;

    /**
     * SyncCategories constructor.
     *
     * @param Account $account
     * @param API $api
     */
    public function __construct(Account $account, API $api)
    {
        $this->account  = $account;
        $this->termMeta = new ExportedToWorkflowTermMeta();
        $this->api      = $api;
        $this->prepareAPI();
    }

    /**
     * Export single term to Workflow.
     *
     * You should pass the required term.
     *
     * @param $term int|\WP_Term Id of term or \WP_Term instance.
     *
     * @throws OutdatedWordPressException If WordPress not support this operation.
     * @throws \Exception If something goes wrong.
     *
     * @return CategoryEntity If term was successfully created.
     */
    public function exportTermToWorkflow($term)
    {
        if (!WordPressFeatures::isTermsMetaSupported()) {
            throw new OutdatedWordPressException();
        }

        if (!Compatibility::checkWordPress('4.6')) {
            throw new OutdatedWordPressException();
        }

        if (is_int($term)) {
            $term = get_term($term, $this->getTaxonomy());
        }

        if (!is_a($term, \WP_Term::class)) {
            throw new \Exception();
        }

        $details = $this->createCategoryAction->configureDetails(array(
            'space' => $this->getAccount()->getSpaceShortName(),
            'options' => array(
                'http_errors' => false,
                'json' => array(
                    'name' => $term->name,
                ),
            ),
        ));

        $categoryEntity = $this->createCategoryAction
            ->setDetails($details)
            ->request()
            ->handleResponse();

        $this->termMeta
            ->setTermId($term->term_id)
            ->updateValue(true);

        return $categoryEntity;
    }

    /**
     *
     * @throws \Exception
     *
     * @return array If term was successful exported.
     */
    public function exportFirstAvailableTermToWorkflow()
    {
        if (!WordPressFeatures::isTermsMetaSupported()) {
            throw new OutdatedWordPressException();
        }

        if (!Compatibility::checkWordPress('4.6')) {
            throw new OutdatedWordPressException();
        }

        // Find first available not synced category.
        $term = $this->findFirstAvailableTerm();
        if ($term) {
            // Configure action.
            $details = $this->createCategoryAction->configureDetails(array(
                'space' => $this->getAccount()->getSpaceShortName(),
                'options' => array(
                    'http_errors' => false,
                    'json' => array(
                        'name' => $term->name,
                    ),
                ),
            ));

            $entity = $this->createCategoryAction
                ->setDetails($details)
                ->request()
                ->handleResponse();

            $this->termMeta
                ->setTermId($term->term_id)
                ->updateValue(true);

            return array(
                'wordpress' => $term,
                'workflow'  => array(
                    'id' => $entity->getId(),
                    'name' => $entity->getName(),
                ),
            );
        } else {
            throw new TermsNotFoundException();
        }
    }

    /**
     * @return \stdClass First available term to export.
     */
    protected function findFirstAvailableTerm()
    {
        if (!is_string($this->getTaxonomy())) {
            throw new \LogicException('Specify taxonomy name.');
        }

        /**
         * @var $wpdb \wpdb
         */
        global $wpdb;

        // Find any terms for given taxonomy.
        // We don't use WP Term Meta here because it caching the result.
        $query = "
SELECT DISTINCT t.*, tt.*
    FROM {$wpdb->terms} AS t

	LEFT JOIN {$wpdb->termmeta}

		ON (
		    t.term_id = {$wpdb->termmeta}.term_id
		    AND
		    {$wpdb->termmeta}.meta_key = %s
		)

	LEFT JOIN {$wpdb->termmeta} AS mt1

		ON (t.term_id = mt1.term_id)

	INNER JOIN {$wpdb->term_taxonomy} AS tt

		ON t.term_id = tt.term_id
		WHERE tt.taxonomy IN (%s)
		AND ( 
  		    {$wpdb->termmeta}.term_id IS NULL 
  			OR 
  			(
  			    mt1.meta_key = %s
  			    AND
  			    mt1.meta_value = '0'
  			)
		)
LIMIT 1";

        $query = $wpdb->prepare(
            $query,
            $this->termMeta->getName(),
            $this->getTaxonomy(),
            $this->termMeta->getName()
        );

        $results = $wpdb->get_results($query);

        if (!empty($results)) {
            return $results[0];
        }

        return null;
    }

    protected function prepareAPI()
    {
        $this->api->getAuth()->setToken($this->getAccount()->getTokenOption()->get());
        $this->createCategoryAction = new CreateCategoryAction($this->api);
    }

    /**
     * @return array With stat data.
     */
    public function getTermsStat()
    {
        /**
         * @var $wpdb \wpdb
         */
        global $wpdb;

        $stat = array(
            'total' => 0,
            'created' => 0,
            'notCreated' => 0,
        );

        $result = wp_count_terms($this->getTaxonomy());

        if (is_numeric($result)) {
            $stat['total'] = (int) $result;
        }

        $query = "
SELECT
	DISTINCT COUNT(*) as created

	FROM {$wpdb->terms} AS t

	LEFT JOIN {$wpdb->termmeta}
		ON (t.term_id = {$wpdb->termmeta}.term_id AND {$wpdb->termmeta}.meta_key = %s)

	LEFT JOIN {$wpdb->termmeta} AS mt1
		ON (t.term_id = mt1.term_id)

	INNER JOIN {$wpdb->term_taxonomy} AS tt
		ON t.term_id = tt.term_id
		WHERE tt.taxonomy IN (%s)
		AND (
  		  mt1.meta_key = %s AND mt1.meta_value = '1'
		)
";

        $query = $wpdb->prepare(
            $query,
            $this->termMeta->getName(),
            $this->getTaxonomy(),
            $this->termMeta->getName()
        );

        $result = $wpdb->get_results($query);

        $stat['created'] = (int) $result[0]->created;

        $stat['notCreated'] = $stat['total'] - $stat['created'];

        return $stat;
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
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
    }

    /**
     * @return string
     */
    public function getTaxonomy()
    {
        return $this->taxonomy;
    }

    /**
     * @param string $taxonomy
     *
     * @return $this For chain calls.
     */
    public function setTaxonomy($taxonomy)
    {
        $this->taxonomy = $taxonomy;
        return $this;
    }
}
