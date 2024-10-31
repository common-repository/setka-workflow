<?php
namespace Setka\Workflow\Admin\Pages\General;

use Setka\Workflow\Plugin;
use Setka\Workflow\Services\Constraints\WordPressNonceConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints;

/**
 * Class SignInType
 */
class SignInType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('token', Type\TextType::class, array(
                'label' => __('API License key', Plugin::NAME),
                'required' => true,
                'constraints' => array(
                    new Constraints\NotBlank(array(
                        'message' => __('Please enter API License key.', Plugin::NAME),
                    )),
                ),
                'attr' => array(
                    'class' => 'setka-workflow-token-text code',
                ),
            ))
            ->add('nonce', Type\HiddenType::class, array(
                'data' => wp_create_nonce(Plugin::NAME .'-sign-up'),
                'constraints' => array(
                    new Constraints\NotBlank(),
                    new WordPressNonceConstraint(array(
                        'name' => Plugin::NAME .'-sign-up',
                    ))
                ),
            ))
            ->add('submitToken', Type\SubmitType::class, array(
                'label' => _x('Start working with Setka Workflow', 'Button label in sign in form', Plugin::NAME),
                'attr' => array('class' => 'button button-primary'),
            ));
    }
}
