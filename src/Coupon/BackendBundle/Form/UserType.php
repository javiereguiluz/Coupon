<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * The form used in the backend to create and manipulate User entities.
 * It includes every User property.
 */
class UserType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('surname')
            ->add('email')
            ->add('password')
            ->add('salt')
            ->add('address')
            ->add('subscribed')
            ->add('created_at')
            ->add('birthday')
            ->add('pin')
            ->add('credit_card')
            ->add('city')
        ;
    }

    public function getName()
    {
        return 'coupon_backendbundle_usertype';
    }
}
