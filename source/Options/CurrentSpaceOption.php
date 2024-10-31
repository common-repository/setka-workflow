<?php
namespace Setka\Workflow\Options;

use Korobochkin\WPKit\Options\Special\AbstractArrayOption;
use Setka\Workflow\Plugin;
use Symfony\Component\Validator\Constraints;

class CurrentSpaceOption extends AbstractArrayOption
{
    /**
     * CurrentSpaceOption constructor.
     */
    public function __construct()
    {
        $this->setName(Plugin::_NAME_ . '_current_space');
    }

    public function buildConstraint()
    {
        return new Constraints\Collection(array(
            'fields' => array(
                'id' => array(
                    new Constraints\NotNull(),
                    new Constraints\Type('numeric'),
                ),
                'name' => array(
                    new Constraints\NotNull(),
                    new Constraints\Type('string'),
                ),
                'short_name' => array(
                    new Constraints\NotNull(),
                    new Constraints\Type('string'),
                ),
                'active' => array(
                    new Constraints\NotNull(),
                    new Constraints\Type('bool'),
                ),
                'created_at' => array(
                    new Constraints\NotNull(),
                    new Constraints\DateTime(),
                ),
                'updated_at' => array(
                    new Constraints\NotNull(),
                    new Constraints\DateTime(),
                ),
            ),
            'allowExtraFields' => true,
        ));
    }
}
