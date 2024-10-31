<?php
namespace Setka\Workflow\AJAX\Actions\ExportCategoriesToWorkflow;

use Setka\Workflow\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class CategoriesNotFoundViolation
 */
class CategoriesNotFoundViolation extends ConstraintViolation
{
    /**
     * CategoriesNotFoundViolation constructor.
     */
    public function __construct()
    {
        parent::__construct(
            __('You don’t have any categories. Create new ones in Posts -> Categories.', Plugin::NAME),
            '',
            array(),
            null,
            null,
            null
        );
    }
}
