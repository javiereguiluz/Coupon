<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\OfferBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Coupon\CityBundle\Entity\City;
use Coupon\OfferBundle\Entity\Offer;
use Coupon\StoreBundle\Entity\Store;

/**
 * Data fixtures for the Offer entity.
 */
class Offers extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    public function getOrder()
    {
        return 30;
    }

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        // Get all the cities and stores from the database
        $cities = $manager->getRepository('CityBundle:City')->findAll();
        $stores = $manager->getRepository('StoreBundle:Store')->findAll();

        foreach ($cities as $city) {
            $stores = $manager->getRepository('StoreBundle:Store')->findByCity($city->getId());

            for ($j=1; $j<=20; $j++) {
                $offer = new Offer();

                $offer->setName($this->getRandomName());
                $offer->setDescription($this->getRandomDescription());
                $offer->setTerms($this->getRandomTerms());
                $offer->setPhoto('photo'.rand(1,20).'.jpg');
                $offer->setPrice(number_format(rand(100, 10000)/100, 2));
                $offer->setDiscount($offer->getPrice() * (rand(10, 70)/100));

                // Offers are published both in the future and in the past
                if (1 == $j) {
                    $date = 'today';
                    $offer->setApproved(true);
                } elseif ($j < 10) {
                    $date = 'now - '.($j-1).' days';
                    // 80% of past offers are set as approved
                    $offer->setApproved((rand(1, 1000) % 10) < 8);
                } else {
                    $date = 'now + '.($j - 10 + 1).' days';
                    $offer->setApproved(true);
                }

                $publishedAt = new \DateTime($date);
                $publishedAt->setTime(23, 59, 59);

                // $expiredAt must be cloned because otherwise its value would
                // be modified by add() method. Remember that values aren't
                // persisted until the flush() method is executed.
                $expiredAt = clone $publishedAt;
                $expiredAt->add(\DateInterval::createFromDateString('24 hours'));

                $offer->setPublishedAt($publishedAt);
                $offer->setExpiredAt($expiredAt);

                $offer->setPurchases(0);
                $offer->setMinimum(rand(25, 100));

                $offer->setCity($city);

                // select randomly a store of the previous city
                $store = $stores[array_rand($stores)];
                $offer->setStore($store);

                $manager->persist($offer);
                $manager->flush();

                // set properl ACL permissions for the offer

                // Get the identity of both the offer and the user
                $objectId  = ObjectIdentity::fromDomainObject($offer);
                $userId = UserSecurityIdentity::fromAccount($store);

                // Check if the offer has defined an ACL previously
                $provider = $this->container->get('security.acl.provider');
                try {
                    $acl = $provider->findAcl($objectId, array($userId));
                } catch (AclNotFoundException $e) {
                    // Create a new ACL for the object
                    $acl = $provider->createAcl($objectId);
                }

                // Delete the (optional) previous ACEs associated with the object
                $aces = $acl->getObjectAces();
                foreach ($aces as $index => $ace) {
                    $acl->deleteObjectAce($index);
                }

                $acl->insertObjectAce($userId, MaskBuilder::MASK_OPERATOR);
                $provider->updateAcl($acl);
            }
        }
    }

    /**
     * Random offer name generator
     */
    private function getRandomName()
    {
        $words = array_flip(array(
            'Lorem', 'Ipsum', 'Sitamet', 'Et', 'At', 'Sed', 'Aut', 'Vel', 'Ut',
            'Dum', 'Tincidunt', 'Facilisis', 'Nulla', 'Scelerisque', 'Blandit',
            'Ligula', 'Eget', 'Drerit', 'Malesuada', 'Enimsit', 'Libero',
            'Penatibus', 'Imperdiet', 'Pendisse', 'Vulputae', 'Natoque',
            'Aliquam', 'Dapibus', 'Lacinia'
        ));

        $numWords = rand(4, 8);

        return implode(' ', array_rand($words, $numWords));
    }

    /**
     * Random offer description generator
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

        $numPhrases = rand(4, 7);

        return implode("\n", array_rand($phrases, $numPhrases));
    }

    /**
     * Random offer terms generator
     */
    private function getRandomTerms()
    {
        $phrases = array_flip(array(
            'Limit 1 per person.',
            'May buy 1 additional as a gift.',
            'Appointment required.',
            '24hr cancellation notice required.',
            'Must use promotional value in 1 visit.',
            'Not redeemable for cash.',
            'In-store only.',
            'Must be 21 or older.',
            'Gratuity not included.',
            'Valid at listed location only.'
        ));

        $numPhrases = rand(3, 5);

        return implode(' ', array_rand($phrases, $numPhrases));
    }
}
