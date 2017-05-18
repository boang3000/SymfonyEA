<?php

namespace Arcanys\EasyAppBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminRegistrationFormType extends BaseType
{
    public function __construct()
    {
        parent::__construct( __CLASS__ );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email')
                ->add('password', 'password')
                ->add('enabled', 'hidden', array('data' => '1'))
                ->add('firstname', 'text', array('label'  => 'First Name'))
                ->add('lastname', 'text', array('label'  => 'Last Name'))
                /*->add('contactnum', 'text', array('label'  => 'Contact Number'))
                ->add('localcontact', 'text', array('label'  => ' Ext. No.'))*/
				->add('company', 'hidden', array('data' => uniqid()));
	}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Arcanys\EasyAppBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'acme_user_registration';
    }
}