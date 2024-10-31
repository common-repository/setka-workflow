<?php
namespace Setka\Workflow\Services\WorkflowAPI;

use GuzzleHttp\Client;
use Setka\Workflow\Services\WordPressHandler;
use Setka\WorkflowSDK\APIFactory;

/**
 * Class WorkflowAPIFactory
 */
class WorkflowAPIFactory
{
    /**
     * Creates the API instance of Setka Workflow.
     *
     * @param $baseUri string Base uri for Setka Workflow API.
     *
     * @return \Setka\WorkflowSDK\API New API instance.
     */
    public static function create($baseUri)
    {
        $client = new Client(array(
            'handler' => new WordPressHandler(),
            'base_uri' => $baseUri,
        ));

        return APIFactory::create('', $client);
    }
}
