<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\OfferBundle\Form\Extranet;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

/**
 * The form used in the extranet to create and manipulate Offer entities.
 * It doesn't include all Offer properties, because some of them are only for
 * internal use.
 */
class OfferType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('terms')
            ->add('photo', 'file', array('required' => false))
            ->add('price', 'money')
            ->add('discount', 'money')
            ->add('minimum')
        ;

        // This form varies depending upon the action where it's used. When the
        // action is 'new'  the object is beaing created and its 'id'  attribute
        // is still null. In this case, add an extra form field called 'accept'
        // that displays a "I accept the terms ..." checkbox.
        // 
        // When the 'id' attribute isn't null, the action is 'edit' and it's
        // no longer necessary to display this extra checkbox.
        if (null == $options['data']->getId()) {
            $builder->add('accept', 'checkbox', array('property_path' => false));

            $builder->addValidator(new CallbackValidator(function(FormInterface $form) {
                if ($form["accept"]->getData() == false) {
                    $form->addError(new FormError('You must accept Terms and Conditions before adding a new offer'));
                }
            }));
        }
    }

    public function getName()
    {
        return 'offer_store';
    }
}
