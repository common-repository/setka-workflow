<?php
namespace Setka\Workflow\Options;

use Korobochkin\WPKit\Options\Special\AbstractArrayOption;
use Setka\Workflow\Plugin;
use Setka\Workflow\Services\PostTypes;
use Symfony\Component\Validator\Constraints;

class WorkflowPostTypesOption extends AbstractArrayOption
{
    /**
     * WorkflowPostTypesOption constructor.
     */
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_workflow_post_types')
            ->setDefaultValue(array('post', 'page'));
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        $postTypes = PostTypes::getPostTypes();
        $postTypes = array_values($postTypes);

        return array(
            new Constraints\NotNull(),
            new Constraints\Choice(array(
                'choices' => $postTypes,
                'multiple' => true,
                'strict' => true,
            ))
        );
    }
}
