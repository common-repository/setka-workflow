<?php
namespace Setka\Workflow\Admin\Pages\General;

use Setka\Workflow\Plugin;
use Setka\Workflow\Services\Constraints\WordPressNonceConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints;

/**
 * Class SettingsType
 */
class SettingsType extends AbstractType
{
    const SIGN_OUT_BUTTON = 'signOut';

    const EXPORT_CATEGORIES_FROM_WP_TO_WF_BUTTON = 'exportCategoriesFromWordPressToWorkflow';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'token',
                Type\TextType::class,
                array(
                    'label'       => __('API License key', Plugin::NAME),
                    'required'    => true,
                    'disabled'    => true,
                    'constraints' => array(
                        new Constraints\NotBlank(),
                        new Constraints\Length(array(
                            'min' => 50,
                            'max' => 50,
                            'allowEmptyString' => false,
                        )),
                    ),
                    'attr'        => array(
                        'class' => 'setka-workflow-token-text code',
                    ),
                )
            )
            ->add(
                'publishInWorkflow',
                Type\CheckboxType::class,
                array(
                    'label'       => __('Publish posts', Plugin::NAME),
                    'required'    => false,
                )
            )
            ->add(
                'postAuthorId',
                Type\ChoiceType::class,
                array(
                    'label' => __('Post Author', Plugin::NAME),
                    'required' => false,
                    'multiple' => false,
                    'placeholder' => _x('Choose an user', 'dropdown select caption', Plugin::NAME),
                    'choice_loader' => new PostAuthorIdChoiceLoader($builder),
                )
            )
            ->add(
                'exportCategoriesAutomatically',
                Type\CheckboxType::class,
                array(
                    'label' => __('Export categories automatically', Plugin::NAME),
                    'required' => false,
                )
            )
            ->add(
                self::EXPORT_CATEGORIES_FROM_WP_TO_WF_BUTTON,
                Type\SubmitType::class,
                array(
                    'label' => __('Export categories', Plugin::NAME),
                    'attr'  => array('class' => 'button button-secondary'),
                )
            )
            ->add(
                'nonce',
                Type\HiddenType::class,
                array(
                    'data'        => wp_create_nonce(Plugin::NAME.'-settings'),
                    'constraints' => array(
                        new Constraints\NotBlank(),
                        new WordPressNonceConstraint(
                            array(
                                'name' => Plugin::NAME.'-settings',
                            )
                        ),
                    ),
                )
            )
            ->add(
                'save',
                Type\SubmitType::class,
                array(
                    'label' => _x('Save', 'Button label in settings form', Plugin::NAME),
                    'attr'  => array('class' => 'button button-primary'),
                )
            )
            ->add(
                self::SIGN_OUT_BUTTON,
                Type\SubmitType::class,
                array(
                    'label' => _x('Change API license key', 'Button label in settings form', Plugin::NAME),
                    'attr'  => array('class' => 'button button-secondary'),
                )
            )
        ;
    }
}
