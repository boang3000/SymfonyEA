<?php

namespace Arcanys\EasyAppBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Validator\Constraint\UserPassword as OldUserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use FOS\UserBundle\Form\Type\ChangePasswordFormType as BaseType;

class ChangepassFormType extends BaseType
{
    public function __construct()
    {
        parent::__construct( __CLASS__ );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (class_exists('Symfony\Component\Security\Core\Validator\Constraints\UserPassword')) {
            $constraint = new UserPassword();
        } else {
            // Symfony 2.1 support with the old constraint class
            $constraint = new OldUserPassword();
        }

        $builder->add('current_password', 'password', array(
            'label' => 'Current Password',
            'translation_domain' => 'FOSUserBundle',
            'mapped' => false,
            'constraints' => $constraint,
        ));
        $builder->add('plainPassword', 'repeated', array(
            'type' => 'password',
            'options' => array('translation_domain' => 'FOSUserBundle'),
            'first_options' => array('label' => 'New Passowrd'),
            'second_options' => array('label' => 'Confirm New Password'),
            'invalid_message' => 'fos_user.password.mismatch',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        /*$resolver->setDefaults(array(
            'data_class' => $this->class,
            'intention'  => 'change_password',
        ));*/
        $resolver->setDefaults(array(
            'data_class' => 'Arcanys\EasyAppBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'fos_user_change_password';
    }
}
