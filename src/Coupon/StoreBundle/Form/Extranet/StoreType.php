<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\StoreBundle\Form\Extranet;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * The form used in the extranet to manipulate Store entities.
 * It doesn't include all Store properties, because some of them are only for
 * internal use.
 */
class StoreType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('login', 'text', array('read_only' => true))

            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Passwords don\'t match',
                'options' => array('label' => 'Password'),
                'required' => false
            ))

            ->add('description')
            ->add('address')
            ->add('city')
        ;
    }

    public function getName()
    {
        return 'coupon_storebundle_storetype';
    }
}
