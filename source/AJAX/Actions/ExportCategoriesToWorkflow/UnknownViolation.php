<?php
namespace Setka\Workflow\AJAX\Actions\ExportCategoriesToWorkflow;

use Setka\Workflow\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class UnknownViolation
 */
class UnknownViolation extends ConstraintViolation
{
    public function __construct()
    {
        parent::__construct(
            __('Oops... Some error occurred while exporting categories. Please contact Setka Workflow support team workflow-help@setka.io.', Plugin::NAME),
            '',
            array(),
            null,
            null,
            null
        );
    }
}
