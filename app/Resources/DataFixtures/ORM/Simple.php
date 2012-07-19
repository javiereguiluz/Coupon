<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Coupon\CityBundle\Entity\City;
use Coupon\OfferBundle\Entity\Offer;
use Coupon\StoreBundle\Entity\Store;
use Coupon\UserBundle\Entity\User;
use Coupon\OfferBundle\Entity\Purchase;

/**
 * This is an alternative and lightweight fixtures file. It doesn't use neither
 * the ACL nor the advanced features of the security component.
 *
 * Use these fixtures when developing Coupon application on your own and you
 * haven't defined yet the security configuration. Load the simple fixtures by
 * executing the following command:
 *
 * $ php app/console doctrine:fixtures:load --fixtures=app/Resources
 * 
 * When loading these fixtures, 'User'  users must store their passwords in
 * plain text. Make sure that the following is configured in your 'security.yml'
 * file:
 * 
 *   security:
 *     # ...
 *     encoders:
 *       Coupon\UserBundle\Entity\User:  plaintext
 */
class Simple implements FixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        // Cities
        foreach (array('New York City', 'Tokyo', 'London', 'Paris') as $name) {
            $city = new City();
            $city->setName($name);
            
            $manager->persist($city);
        }

        $manager->flush();
        
        // Stores
        $cities = $manager->getRepository('CityBundle:City')->findAll();
        $storeNumber = 0;
        foreach ($cities as $city) {
            for ($i=1; $i<=10; $i++) {
                $storeNumber++;
                
                $store = new Store();
                $store->setName('Store #'.$storeNumber);
                $store->setLogin('store'.$storeNumber);
                $store->setPassword('password'.$storeNumber);
                $store->setSalt(md5(time()));
                $store->setDescription(
                    "Lorem ipsum dolor sit amet, consectetur adipisicing elit,"
                    ."sed do eiusmod tempor incididunt ut labore et dolore magna"
                    ."aliqua. Ut enim ad minim veniam, quis nostrud exercitation"
                    ."ullamco laboris nisi ut aliquip ex ea commodo consequat."
                );
                $store->setAddress("$i Lorem Ipsum Street\n".$city->getName());
                $store->setCity($city);
                
                $manager->persist($store);
            }
        }
        $manager->flush();
        
        // Offers
        $cities = $manager->getRepository('CityBundle:City')->findAll();
        $offerNumber = 0;
        foreach ($cities as $city) {
            $stores = $manager->getRepository('StoreBundle:Store')->findByCity($city->getId());

            for ($i=1; $i<=50; $i++) {
                $offerNumber++;
                
                $offer = new offer();
                
                $offer->setName('Offer #'.$offerNumber.' lorem ipsum dolor sit amet');
                $offer->setDescription(
                    "Lorem ipsum dolor sit amet, consectetur adipisicing.\n"
                    ."Elit, sed do eiusmod tempor incididunt.\n"
                    ."Ut labore et dolore magna aliqua.\n"
                    ."Nostrud exercitation ullamco laboris nisi ut"
                );
                $offer->setTerms("Labore et dolore magna aliqua. Ut enim ad minim veniam.");
                $offer->setPhoto('photo'.rand(1,20).'.jpg');
                $offer->setPrice(number_format(rand(100, 10000)/100, 2));
                $offer->setDiscount($offer->getPrice() * (rand(10, 70)/100));
                
                // Offers are published both in the future and in the past
                if (1 == $i) {
                    $date = 'today';
                    $offer->setApproved(true);
                }
                elseif ($i < 10) {
                    $date = 'now - '.($i-1).' days';
                    // 80% of past offers are set as approved
                    $offer->setApproved((rand(1, 1000) % 10) < 8);
                }
                else {
                    $date = 'now + '.($i - 10 + 1).' days';
                    $offer->setApproved(true);
                }

                $publishedAt = new \DateTime($date);
                $publishedAt->setTime(23, 59, 59);
                
                $expiredAt = clone $publishedAt;
                $expiredAt->add(\DateInterval::createFromDateString('24 hours'));
                
                $offer->setPublishedAt($publishedAt);
                $offer->setExpiredAt($expiredAt);
                
                $offer->setPurchases(0);
                $offer->setMinimum(rand(25, 100));
                
                $offer->setCity($city);
                
                $offer->setStore($stores[array_rand($stores)]);
                
                $manager->persist($offer);
            }
        }
        $manager->flush();
        
        // Users
        $userNumber = 0;
        foreach ($cities as $city) {
            for ($i=1; $i<=100; $i++) {
                $userNumber++;
                
                $user = new User();
                
                $user->setName('User #'.$userNumber);
                $user->setSurname('Smith');
                $user->setEmail('user'.$userNumber.'@localhost');
                $user->setSalt('');
                $user->setPassword('password'.$userNumber);
                $user->setAddress("$i Ipsum Lorem Street\n".$city->getName());
                // 60% of users are subscribed to the newsletter
                $user->setSubscribed((rand(1, 1000) % 10) < 6);
                $user->setCreatedAt(new \DateTime('now - '.rand(1, 150).' days'));
                $user->setBirthday(new \DateTime('now - '.rand(7000, 20000).' days'));
                
                // check src/Coupon/UserBundle/Entity/User.php for details about the $pin property
                $pin = substr(rand(), 0, 8);
                $user->setPin($pin.substr(
                    "TRWAGMYFPDXBNJZSQVHLCKE",
                    strtr($pin, "XYZ", "012")%23, 1)
                );
                
                $user->setCreditCard('1234567890123456');
                $user->setCity($city);
                
                $manager->persist($user);
            }
        }
        $manager->flush();
        
        // Purchases
        $offers = $manager->getRepository('OfferBundle:Offer')->findAll();
        $users  = $manager->getRepository('UserBundle:User')->findAll();
        
        foreach ($users as $user) {
            $purchases = rand(0, 10);
            $bought = array();

            for ($i=0; $i<$purchases; $i++) {
                $purchase = new Purchase();

                $purchase->setCreatedAt(new \DateTime('now - '.rand(0, 250).' hours'));

                // pick an offer randomly ...
                $offer = $offers[array_rand($offers)];
                
                // .. but check that it meets the conditions for a valid purchase:
                //   - a user can't purchase the same offer more than one time
                //   - the offer must be approved
                //   - the offer must be published
                while (in_array($offer->getId(), $bought)
                       || $offer->isApproved() == false
                       || $offer->getPublishedAt() > new \DateTime('now')) {
                    $offer = $offers[array_rand($offers)];
                }
                $bought[] = $offer->getId();

                $purchase->setOffer($offer);
                $purchase->setUser($user);

                $manager->persist($purchase);

                $offer->setPurchases($offer->getPurchases() + 1);
                $manager->persist($offer);
            }

            unset($bought);
        }
        $manager->flush();
    }
}