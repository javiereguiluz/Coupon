<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\OfferBundle\Tests\Twig\Extension;

use Coupon\OfferBundle\Twig\Extension\CouponExtension;


/**
 * This unit test checks that the custom Twig extension works as expected.
 */
class TwigExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testDescuento()
    {
        $extension = new CouponExtension();

        $this->assertEquals('-', $extension->discount(100, null),
            'Discount cannot be null'
        );
        $this->assertEquals('-', $extension->discount('a', 3),
            'Price must be a number'
        );
        $this->assertEquals('-', $extension->discount(100, 'a'),
            'Discount must be a number'
        );

        $this->assertEquals('0%', $extension->discount(10, 0),
            'A zero discount is displayed as 0%'
        );
        $this->assertEquals('-80%', $extension->discount(2, 8),
            'If the price is 2 euros and the discount from the original price is
            8 euros, the discount is -80%'
        );
        $this->assertEquals('-33%', $extension->discount(10, 5),
            'If the price is 10 euros and the discount from the original price is
            5 euros, the discount is -33%'
        );
        $this->assertEquals('-33.33%', $extension->discount(10, 5, 2),
            'If the price is 10 euros and the discount from the original price is
            5 euros, the discount is -33.33% with two decimals'
        );
    }

    public function testShowAsList()
    {
        $fixtures = __DIR__.'/fixtures/list';
        $extension = new CouponExtension();

        $original = file_get_contents($fixtures.'/original.txt');

        $this->assertEquals(
            file_get_contents($fixtures.'/expected-ul.txt'),
            $extension->showAsList($original)
        );

        $this->assertEquals(
            file_get_contents($fixtures.'/expected-ol.txt'),
            $extension->showAsList($original, 'ol')
        );
    }
}
