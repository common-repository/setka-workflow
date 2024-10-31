<?php
namespace Setka\Workflow\Admin\MetaBoxes\WorkflowID;

use Setka\Workflow\Plugin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * Class WorkflowIdType
 */
class WorkflowIdType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', Type\TextType::class, array(
                'label' => __('Ticket Url', Plugin::NAME),
                'required' => false,
                'disabled' => false,
                'attr' => array(
                    'class' => 'large-text',
                ),
            ))
        ;
    }
}
