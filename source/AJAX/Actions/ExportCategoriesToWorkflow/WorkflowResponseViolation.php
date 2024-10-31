<?php
namespace Setka\Workflow\AJAX\Actions\ExportCategoriesToWorkflow;

use Setka\Workflow\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class WorkflowResponseViolation
 */
class WorkflowResponseViolation extends ConstraintViolation
{
    /**
     * WorkflowResponseViolation constructor.
     */
    public function __construct()
    {
        parent::__construct(
            __('Oops... Setka Workflow cannot receive these categories. Please contact Setka Workflow support team workflow-help@setka.io.', Plugin::NAME),
            '',
            array(),
            null,
            null,
            null
        );
    }
}
