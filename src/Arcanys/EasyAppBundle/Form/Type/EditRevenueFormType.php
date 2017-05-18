<?php

namespace Arcanys\EasyAppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Arcanys\EasyAppBundle\Entity\RevenueRepository;

class EditRevenueFormType extends AbstractType
{
    private $company;
    public function __construct($company) {
        $this->company = $company[0];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $company = $this->company;

        $builder->add('dateadded', 'text', array(
						'required' => true,
						'label' => 'Date',
					))
				->add('entityid', 'entity', array(
						'class' => 'ArcanysEasyAppBundle:Entity',
                        'query_builder' => function($repository) use ($company) {
                                $qb = $repository->createQueryBuilder('r');
                                return $qb
                                    ->where('r.company = :company')
                                    ->setParameter('company', $company)
                                    ;
                            },
						'label' => 'Entity',
						'required'  => true,
						'empty_value' => 'Choose an Entity',
					))
                ->add('amount', 'text', array('label' => 'Amount'))
                ->add('description', 'textarea', array('label' => 'Description'))
				->add('upltoken', 'hidden')
				->add('Save', 'submit');
    }
	public function getName()
    {
        return 'revenue';
    }
}