<?php
namespace Setka\Workflow\Options;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Workflow\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Class TokenOption
 */
class TokenOption extends AbstractOption
{
    /**
     * TokenOption constructor.
     */
    public function __construct()
    {
        $this->setName(Plugin::_NAME_ . '_token');
    }

    public function buildConstraint()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\Length(array(
                'min' => 50,
                'max' => 50,
                'allowEmptyString' => false,
            )),
        );
    }
}
