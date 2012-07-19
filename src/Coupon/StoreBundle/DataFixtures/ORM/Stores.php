<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\StoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Coupon\CityBundle\Entity\City;
use Coupon\StoreBundle\Entity\Store;

/**
 * Data fixtures for the Store entity.
 */
class Stores extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    public function getOrder()
    {
        return 20;
    }

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        // Get all the cities from the database
        $cities = $manager->getRepository('CityBundle:City')->findAll();

        $i = 1;
        foreach ($cities as $city) {
            $numStores = rand(2, 5);

            for ($j=1; $j<=$numStores; $j++) {
                $store = new Store();

                $store->setName($this->getRandomName());

                $store->setLogin('store'.$i);
                $store->setSalt(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36));

                $plainPassword = 'store'.$i;
                $encoder = $this->container->get('security.encoder_factory')->getEncoder($store);
                $hashedPassword = $encoder->encodePassword($plainPassword, $store->getSalt());
                $store->setPassword($hashedPassword);

                $store->setDescription($this->getRandomDescription());
                $store->setAddress($this->getRandomAddress($city));
                $store->setCity($city);

                $manager->persist($store);

                $i++;
            }
        }

        $manager->flush();
    }

    /**
     * Random store name generator 
     */
    private function getRandomName()
    {
        $suffix = array('Restaurant', 'Cafe', 'Bar', 'Pub', 'Pizza', 'Burger');
        $names = array(
            'Lorem ipsum', 'Sit amet', 'Consectetur', 'Adipiscing elit',
            'Nec sapien', 'Tincidunt', 'Facilisis', 'Nulla scelerisque',
            'Blandit ligula', 'Eget', 'Hendrerit', 'Malesuada', 'Enim sit'
        );

        return $names[array_rand($names)].' '.$suffix[array_rand($suffix)];
    }

    /**
     * Random store description generator
     */
    private function getRandomDescription()
    {
        $phrases = array_flip(array(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'Mauris ultricies nunc nec sapien tincidunt facilisis.',
            'Nulla scelerisque blandit ligula eget hendrerit.',
            'Sed malesuada, enim sit amet ultricies semper, elit leo lacinia massa, in tempus nisl ipsum quis libero.',
            'Aliquam molestie neque non augue molestie bibendum.',
            'Pellentesque ultricies erat ac lorem pharetra vulputate.',
            'Donec dapibus blandit odio, in auctor turpis commodo ut.',
            'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.',
            'Nam rhoncus lorem sed libero hendrerit accumsan.',
            'Maecenas non erat eu justo rutrum condimentum.',
            'Suspendisse leo tortor, tempus in lacinia sit amet, varius eu urna.',
            'Phasellus eu leo tellus, et accumsan libero.',
            'Pellentesque fringilla ipsum nec justo tempus elementum.',
            'Aliquam dapibus metus aliquam ante lacinia blandit.',
            'Donec ornare lacus vitae dolor imperdiet vitae ultricies nibh congue.',
        ));

        $numPhrases = rand(3, 6);

        return implode(' ', array_rand($phrases, $numPhrases));
    }

    /**
     * Random store address generator
     */
    private function getRandomAddress($city)
    {
        $suffix = array('Street', 'Avenue', 'Boulevard');
        $names = array(
            'Lorem', 'Ipsum', 'Sitamet', 'Consectetur', 'Adipiscing',
            'Necsapien', 'Tincidunt', 'Facilisis', 'Nulla', 'Scelerisque',
            'Blandit', 'Ligula', 'Eget', 'Hendrerit', 'Malesuada', 'Enimsit'
        );

        return rand(1, 500).' '.$names[array_rand($names)].' '.$suffix[array_rand($suffix)]."\n"
               .$this->getRandomZip().' '.$city->getName();
    }

    /**
     * Random store ZIP generator
     */
    private function getRandomZip()
    {
        return sprintf('%02s%03s', rand(0, 99), rand(0, 999));
    }
}
