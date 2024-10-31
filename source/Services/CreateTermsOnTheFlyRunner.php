<?php
namespace Setka\Workflow\Services;

use Korobochkin\WPKit\Runners\AbstractRunner;

/**
 * Class CreateTermsOnTheFlyRunner
 */
class CreateTermsOnTheFlyRunner extends AbstractRunner
{
    /**
     * @inheritdoc
     */
    public static function run()
    {
    }

    /**
     * Connected to created_term action.
     *
     * @param $termId int Term ID.
     * @param $ttId int Term taxonomy ID.
     * @param $taxonomy string Taxonomy name.
     */
    public static function createdTerm($termId, $ttId, $taxonomy)
    {
        /**
         * @var $createTerms CreateTermsOnTheFly
         */
        $createTerms = self::getContainer()->get(CreateTermsOnTheFly::class);
        $createTerms->run($termId, $ttId, $taxonomy);
    }
}
