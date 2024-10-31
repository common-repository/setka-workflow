<?php
namespace Setka\Workflow\Admin\Pages\General;

use Setka\Workflow\Services\UserQuery;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PostAuthorIdChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * %1$s - First Name + Last Name
     * %2$s - Email
     * %3$s - ID
     */
    const LABEL_PATTERN = '%1$s <%2$s> [ID=%3$s]';

    /**
     * The loaded choice list.
     *
     * @var ArrayChoiceList
     */
    protected $choiceList;

    /**
     * @var FormBuilderInterface
     */
    protected $builder;

    /**
     * @var integer
     */
    protected $selected;

    /**
     * PostAuthorIdChoiceLoader constructor.
     *
     * @param $builder FormBuilderInterface
     */
    public function __construct(FormBuilderInterface $builder = null)
    {
        $this->builder = $builder;

        if (is_object($builder)) {
            // Let the form builder notify us about initial/submitted choices
            $builder->addEventListener(
                FormEvents::POST_SET_DATA,
                array($this, 'onFormPostSetData')
            );

            $builder->addEventListener(
                FormEvents::POST_SUBMIT,
                array($this, 'onFormPostSetData')
            );
        }
    }

    /**
     * Form submit event callback
     * Here we get notified about the submitted choices.
     * Remember them so we can add them in loadChoiceList().
     *
     * @param $event FormEvent
     */
    public function onFormPostSetData(FormEvent $event)
    {
        $this->selected = array();

        $data = $event->getData();

        if (!is_object($data)) {
            return;
        }

        $this->selected = $data->getPostAuthorId();
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceList($value = null)
    {
        if (null !== $this->choiceList) {
            return $this->choiceList;
        }

        $choices = array();

        $users = UserQuery::createLast10()->get_results();
        foreach ($users as $user) {
            /**
             * @var $user \WP_User
             */
            $userLabel = sprintf(
                self::LABEL_PATTERN,
                $user->data->display_name,
                $user->data->user_email,
                $user->data->ID
            );

            $choices[$userLabel] = (int) $user->data->ID;
        }
        unset($users, $user, $userLabel);

        $missingFlipped = array_flip($choices);

        if (!isset($missingFlipped[$this->selected])) {
            $users = UserQuery::search20($this->selected)->get_results();
            foreach ($users as $user) {
                /**
                 * @var $user \WP_User
                 */
                $userLabel = sprintf(
                    self::LABEL_PATTERN,
                    $user->data->display_name,
                    $user->data->user_email,
                    $user->data->ID
                );

                $choices[$userLabel] = (int) $user->data->ID;
            }
        }

        return $this->choiceList = new ArrayChoiceList($choices, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        $result = array();

        foreach ($values as $id) {
            $user = get_user_by('ID', $id);
            if ($user) {
                $result[] = $id;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        $result = array();

        foreach ($choices as $id) {
            $user = get_user_by('ID', $id);
            if ($user) {
                $result[] = $id;
            }
        }

        return $result;
    }
}
