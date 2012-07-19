<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\OfferBundle\Tests;

use Symfony\Component\Validator\ValidatorFactory;
use Coupon\OfferBundle\Entity\Offer;
use Coupon\CityBundle\Entity\City;
use Coupon\StoreBundle\Entity\Store;

/**
 * This unit test checks that the Offer entity works as expected.
 */
class OfferTest extends \PHPUnit_Framework_TestCase
{
    private $validator;

    protected function setUp()
    {
        $this->validator = ValidatorFactory::buildDefault()->getValidator();
    }

    public function testValidSlug()
    {
        $offer = new Offer();

        $offer->setName('Sample offer');
        $slug = $offer->getSlug();

        $this->assertEquals('sample-offer', $slug, 'The slug is generated automatically');
    }

    public function testValidDescription()
    {
        $offer = new Offer();
        $offer->setName('Sample offer');

        list($errors, $error) = $this->validate($offer);

        $this->assertGreaterThan(0, count($errors), 'Description cannot be blank');
        $this->assertEquals('This value should not be blank', $error->getMessageTemplate());
        $this->assertEquals('description', $error->getPropertyPath());

        $offer->setDescription('Sample description');
        list($errors, $error) = $this->validate($offer);

        $this->assertGreaterThan(0, count($errors), 'Description cannot be too short (30 characters at least');
        $this->assertRegExp("/This value is too short/", $error->getMessageTemplate());
        $this->assertEquals('description', $error->getPropertyPath());
    }

    public function testValidDates()
    {
        $offer = new Offer();
        $offer->setName('Sample offer');
        $offer->setDescription('Sample description - Long enough to be considered as a valid description');

        $offer->setPublishedAt(new \DateTime('today'));
        $offer->setExpiredAt(new \DateTime('yesterday'));
        list($errors, $error) = $this->validate($offer);

        $this->assertGreaterThan(0, count($errors), 'Expiration date must be set after the publication date');
        $this->assertEquals('Expiration date must be set after the publication date', $error->getMessageTemplate());
        $this->assertEquals('validDate', $error->getPropertyPath());
    }

    public function testValidMinimum()
    {
        $offer = new Offer();
        $offer->setName('Sample offer');
        $offer->setDescription('Sample description - Long enough to be considered as a valid description');
        $offer->setPublishedAt(new \DateTime('today'));
        $offer->setExpiredAt(new \DateTime('tomorrow'));

        $offer->setMinimum(3.5);
        list($errors, $error) = $this->validate($offer);

        $this->assertGreaterThan(0, count($errors), 'The minimum number of purchases must be an integer');
        $this->assertRegExp("/This value should be of type/", $error->getMessageTemplate());
        $this->assertEquals('minimum', $error->getPropertyPath());
    }

    public function testValidPrice()
    {
        $offer = new Offer();
        $offer->setName('Sample offer');
        $offer->setDescription('Sample description - Long enough to be considered as a valid description');
        $offer->setPublishedAt(new \DateTime('today'));
        $offer->setExpiredAt(new \DateTime('tomorrow'));
        $offer->setMinimum(3);

        $offer->setPrice(-10);
        list($errors, $error) = $this->validate($offer);

        $this->assertGreaterThan(0, count($errors), 'Price cannot be a negative value');
        $this->assertRegExp("/This value should be .* or more/", $error->getMessageTemplate());
        $this->assertEquals('price', $error->getPropertyPath());
    }

    public function testValidCity()
    {
        $offer = new Offer();
        $offer->setName('Sample offer');
        $offer->setDescription('Sample description - Long enough to be considered as a valid description');
        $offer->setPublishedAt(new \DateTime('today'));
        $offer->setExpiredAt(new \DateTime('tomorrow'));
        $offer->setMinimum(3);
        $offer->setPrice(10.5);

        $offer->setCity($this->getCity());
        $citySlug = $offer->getCity()->getSlug();

        $this->assertEquals('sample-city', $citySlug, 'Offer stores its city correctly');
    }

    public function testValidStore()
    {
        $offer = new Offer();
        $offer->setName('Sample offer');
        $offer->setDescription('Sample description - Long enough to be considered as a valid description');
        $offer->setPublishedAt(new \DateTime('today'));
        $offer->setExpiredAt(new \DateTime('tomorrow'));
        $offer->setMinimum(3);
        $offer->setPrice(10.5);
        $city = $this->getCity();
        $offer->setCity($city);

        $offer->setStore($this->getStore($city));
        $offer_city = $offer->getCity()->getName();
        $offer_store_city = $offer->getStore()->getCity()->getName();

        $this->assertEquals($offer_city, $offer_store_city, 'The offer store is associated with the same city as the offer itself');
    }

    private function validate(Offer $offer)
    {
        $errors = $this->validator->validate($offer);
        $error = $errors[0];

        return array($errors, $error);
    }

    private function getCity()
    {
        $city = new City();
        $city->setName('Sample city');

        return $city;
    }

    private function getStore($city)
    {
        $store = new Store();
        $store->setName('Sample store');
        $store->setCity($city);

        return $store;
    }

}
