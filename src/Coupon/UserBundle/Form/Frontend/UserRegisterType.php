<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\UserBundle\Form\Frontend;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

/**
 * The form used to register new users in the public website. 
 */
class UserRegisterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('surname')
            ->add('email', 'email',  array('label' => 'Email address', 'attr' => array(
                'placeholder' => 'user@server'
            )))

            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Passwords don\'t match',
                'options' => array('label' => 'Password'),
                'required' => false
            ))

            ->add('address')
            ->add('subscribed', 'checkbox', array('required' => false))
            ->add('birthday', 'birthday', array(
                'years' => range(date('Y') - 18, date('Y') - 18 - 120)
            ))
            ->add('pin')
            ->add('credit_card', 'text', array('label' => 'Credit/Debit Card Number', 'attr' => array(
                'pattern' => '^[0-9]{13,16}$',
                'placeholder' => 'Between 13 and 16 digits'
            )))

            ->add('city', 'entity', array(
                'class' => 'Coupon\\CityBundle\\Entity\\City',
                'empty_value' => 'Select your city',
                'query_builder' => function(EntityRepository $repository) {
                    return $repository->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ))
        ;
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'validation_groups' => array('default', 'register')
        );
    }

    public function getName()
    {
        return 'frontend_user';
    }
}
