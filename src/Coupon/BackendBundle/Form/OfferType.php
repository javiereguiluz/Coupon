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
 * The form used in the backend to create and manipulate Offer entities.
 * It includes every Offer property.
 */
class OfferType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('slug')
            ->add('description')
            ->add('terms')
            ->add('photo')
            ->add('price', 'money')
            ->add('discount', 'money')
            ->add('published_at')
            ->add('expired_at')
            ->add('purchases', 'integer')
            ->add('minimum', 'integer', array('label' => 'Purchases needed to activate the offer'))
            ->add('approved', null, array('required' => false))
            ->add('city')
            ->add('store')
        ;
    }

    public function getName()
    {
        return 'coupon_backendbundle_offertype';
    }
}
