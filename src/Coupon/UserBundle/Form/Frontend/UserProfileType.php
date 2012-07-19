<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\UserBundle\Form\Frontend;

use Coupon\UserBundle\Form\Frontend\UserRegistroType;

/**
 * The form used to view/edit user profile. The form is the same as the registration
 * form, but it uses a different validation. Therefore, this form just extends
 * the UserRegisterType and overrides getDefaultOptions() method.
 */
class UserProfileType extends UserRegisterType
{
    public function getDefaultOptions(array $options)
    {
        return array(
            'validation_groups' => array('default')
        );
    }
}
