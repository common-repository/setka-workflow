<?php
namespace Setka\Workflow\AJAX\Actions\ExportCategoriesToWorkflow;

use Setka\Workflow\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class AllCategoriesExportedViolation
 */
class AllCategoriesExportedViolation extends ConstraintViolation
{
    /**
     * AllCategoriesExportedViolation constructor.
     */
    public function __construct()
    {
        parent::__construct(
            __('All the categories are already exported to Setka Workflow. You don’t have any new categories to export.', Plugin::NAME),
            '',
            array(),
            null,
            null,
            null
        );
    }
}
