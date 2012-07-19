<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Coupon\CityBundle\Entity\City;
use Coupon\UserBundle\Entity\User;

/**
 * Data fixtures for the User entity.
 */
class Users extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    public function getOrder()
    {
        return 40;
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

        for ($i=1; $i<=500; $i++) {
            $user = new User();

            $user->setName($this->getRandomName());
            $user->setSurname($this->getRandomSurname());
            $user->setEmail('user'.$i.'@localhost');

            $user->setSalt(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36));

            $plainPassword = 'user'.$i;
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
            $hashedPassword = $encoder->encodePassword($plainPassword, $user->getSalt());
            $user->setPassword($hashedPassword);

            $city = $cities[array_rand($cities)];
            $user->setAddress($this->getRandomAddress($city));
            $user->setCity($city);

            // 60% of users are subscribed to the newsletter
            $user->setSubscribed((rand(1, 1000) % 10) < 6);

            $user->setCreatedAt(new \DateTime('now - '.rand(1, 150).' days'));
            $user->setBirthday(new \DateTime('now - '.rand(7000, 20000).' days'));

            // check src/Coupon/UserBundle/Entity/User.php for details about the $pin property
            $pin = substr(rand(), 0, 8);
            $user->setPin($pin.substr("TRWAGMYFPDXBNJZSQVHLCKE", strtr($pin, "XYZ", "012")%23, 1));

            $user->setCreditCard('1234567890123456');

            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * Random user name generator.
     * It aproximately generates 50% males and 50% females.
     */
    private function getRandomName()
    {
        // The most popular male/female names in the world (in the recent years)
        // source: http://en.wikipedia.org/wiki/List_of_most_popular_given_names

        $males = array(
            'Mohamed', 'Ahmed', 'Alok', 'Noam', 'Adam', 'Minjun', 'Ren',
            'Hiroto', 'Narek', 'Maximilian', 'Yusif', 'Ivan', 'Lars',
            'Luka', 'Jakub', 'William', 'David', 'Oliver', 'Elias',
            'Ben', 'Georgios', 'Malik', 'Áron', 'Jack', 'Francesco', 'Daan',
            'Emil', 'Szymon', 'João', 'Andrei', 'Daniil', 'Pablo', 'Iker',
            'Marc', 'Miguel', 'Liam', 'Ethan', 'Santiago'
        );
        $females = array(
            'Fatima', 'Lamar', 'Noa', 'María', 'Yua', 'Yui', 'Nur', 'Mane',
            'Lena', 'Emma', 'Viktoria', 'Tereza', 'Olivia', 'María', 'Ida',
            'Mia', 'Emily', 'Sofia', 'Sarah', 'Anastasia', 'Lucía', 'Ane',
            'Martina', 'Alice', 'Júlia', 'Ava', 'Gabrielle',
            'Mary', 'Patricia', 'Linda', 'Barbara'
        );

        if (rand() % 2) {
            return $males[array_rand($males)];
        } else {
            return $females[array_rand($females)];
        }
    }

    /**
     * Random user surname generator.
     */
    private function getRandomSurname()
    {
        // The most popular surnames in the world (in the recent years)
        // source: http://en.wikipedia.org/wiki/Lists_of_most_common_surnames

        $surnames = array(
            'Wáng', 'Li', 'Zhāng', 'Liú', 'Chén', 'Yáng', 'Agarwal', 'Arain',
            'Amin', 'Cohen', 'Levi', 'Sato', 'Suzuki', 'Takahashi', 'Tanaka',
            'Kim', 'Lee', 'Park', 'Choi', 'Gruber', 'Peeters', 'Janssens',
            'Dimitrov', 'Kovacevic', 'Novák', 'Jensen', 'Nielsen', 'Korhonen',
            'Martin', 'Bernard', 'Dubois', 'Müller', 'Schmidt', 'Schneider',
            'Papadopoulos', 'Nagy', 'O\'Sullivan', 'Rossi', 'Kazlauskas',
            'De Jong', 'Jansen', 'Hansen', 'Kowalski', 'Almeida', 'Smirnov',
            'Ivanov', 'García', 'Fernández', 'Johansson', 'Smith', 'Jones',
            'Taylor', 'Brown', 'Wilson'
        );

        return $surnames[array_rand($surnames)];
    }

    /**
     * Random user address generator
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
