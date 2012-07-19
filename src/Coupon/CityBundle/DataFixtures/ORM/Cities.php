<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\CityBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Coupon\CityBundle\Entity\City;

/**
 * Data fixtures for the City entity.
 */
class Cities extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 10;
    }

    public function load(ObjectManager $manager)
    {
        // The 25 most populous cities in the world
        // source: http://en.wikipedia.org/wiki/List_of_cities_proper_by_population

        $cities = array(
            'Shanghai',
            'Istanbul',
            'Karachi',
            'Mumbai',
            'Beijing',
            'Moscow',
            'São Paulo',
            'Tianjin',
            'Guangzhou',
            'Delhi',
            'Seoul',
            'Shenzhen',
            'Jakarta',
            'Tokyo',
            'Mexico City',
            'Kinshasa',
            'Tehran',
            'Bangalore',
            'New York City',
            'Dongguan',
            'Lagos',
            'London',
            'Lima',
            'Bogotá',
            'Ho Chi Minh City'
        );

        foreach ($cities as $name) {
            $city = new City();
            $city->setName($name);

            $manager->persist($city);
        }

        $manager->flush();
    }
}
