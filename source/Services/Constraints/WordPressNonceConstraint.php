<?php
namespace Setka\Workflow\Services\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class WordPressNonceConstraint extends Constraint
{
    /**
     * @var $message string Error message.
     */
    public $message = 'The nonce is not valid.';

    /**
     * @var string Name of nonce field.
     */
    public $name = '_wpnonce';

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'name';
    }
}
