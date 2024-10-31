<?php
namespace Setka\Workflow\AJAX\Actions;

use Setka\Workflow\AJAX\AbstractAction;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SaveWorkflowIdFromMetaBoxAction
 */
class SaveWorkflowIdFromMetaBoxAction extends AbstractAction
{
    /**
     * SaveWorkflowIdFromMetaBoxAction constructor.
     */
    public function __construct()
    {
        $this
            ->setName(self::class)
            ->setEnabledForLoggedIn(true);
    }

    /**
     * @inheritdoc
     */
    public function handleRequest()
    {
        $data     = array();
        $request  = $this->getRequest();
        $response = $this->getResponse();



        $this->getResponse()
             ->setData($data)
             ->setStatusCode(Response::HTTP_OK);

        return $this;
    }
}
