<?php
namespace Setka\Workflow\AJAX\Actions\ExportCategoriesToWorkflow;

use Setka\Workflow\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class ConnectionViolation
 */
class ConnectionViolation extends ConstraintViolation
{
    /**
     * ConnectionViolation constructor.
     */
    public function __construct()
    {
        parent::__construct(
            __('Oops... We couldn’t export the categories because Setka Workflow server is not available now. Please try a little bit later or contact Setka Workflow support team workflow-help@setka.io.', Plugin::NAME),
            '',
            array(),
            null,
            null,
            null
        );
    }
}
