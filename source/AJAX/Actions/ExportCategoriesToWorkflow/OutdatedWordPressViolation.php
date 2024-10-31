<?php
namespace Setka\Workflow\AJAX\Actions\ExportCategoriesToWorkflow;

use Setka\Workflow\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class OutdatedWordPressViolation
 */
class OutdatedWordPressViolation extends ConstraintViolation
{
    /**
     * OutdatedWordPressViolation constructor.
     */
    public function __construct()
    {
        parent::__construct(
            __('Your WordPress not support this operation. Please update WordPress at least 4.6 version.', Plugin::NAME),
            '',
            array(),
            null,
            null,
            null
        );
    }
}
