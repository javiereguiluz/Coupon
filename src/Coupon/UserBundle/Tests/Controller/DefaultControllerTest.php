<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * This functional test checks the user registration process and the user
 * profile form.
 */

class DefaultControllerTest extends WebTestCase
{
    private $em;

    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @dataProvider Users
     */
    public function testUserRegisterAndProfile($user)
    {
        $client = static::createClient();
        $client->followRedirects(true);

        $crawler = $client->request('GET', '/');

        // Sign up as new user
        $registerLink = $crawler->selectLink('Sign up now')->link();
        $crawler = $client->click($registerLink);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Sign up FREE as user")')->count(),
            'The registration form is displayed after clicking Sing up button'
        );

        // The following is a trick to obtain the 'id' of any valid city. As
        // fixtures loading sometimes doesn't reset autoincrement values, it's
        // impossible to know if '1' will be a valid 'id' value for a city.
        // 
        // The trick consists of getting a valid 'id' through the <select> list
        // of cities included in every page.
        $selectList = $crawler
            ->selectButton('Sign up')
            ->form()
            ->get("frontend_user[city]")
        ;
        $citiesIds = $selectList->availableOptionValues();
        $anyValidCityId = $citiesIds[1];
        $user['frontend_user[city]'] = $anyValidCityId;

        $form = $crawler->selectButton('Sign up')->form($user);
        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());

        // Chech that the client now has a session cookie
        $this->assertRegExp('/(\d|[a-z])+/', $client->getCookieJar()->get('PHPSESSID')->getValue(),
            'Application has sent a session cookie'
        );

        // Browse to the profile of the new user
        $profile = $crawler->filter('aside section#login')->selectLink('View/edit my profile')->link();
        $crawler = $client->click($profile);

        $this->assertEquals(
            $user['frontend_user[email]'],
            $crawler->filter('form input[name="frontend_user[email]"]')->attr('value'),
            'User data saved in the database match the data used in the registration form'
        );

        // Delete the sample user used in the test
        $user = $this->em->getRepository('UserBundle:User')->findOneByEmail($user['frontend_user[email]']);
        $this->em->remove($user);
        $this->em->flush();
    }

    /**
     * This method provides sample user data
     */
    public function users()
    {
        return array(
            array(
                array(
                    'frontend_user[name]'             => 'Anonoymous',
                    'frontend_user[surname]'          => 'Surname',
                    'frontend_user[email]'            => 'anonymous'.uniqid().'@localhost.localdomain',
                    'frontend_user[password][first]'  => 'anonymous1234',
                    'frontend_user[password][second]' => 'anonymous1234',
                    'frontend_user[address]'          => '123 My Street, 01001 My City',
                    'frontend_user[birthday][day]'    => '01',
                    'frontend_user[birthday][month]'  => '01',
                    'frontend_user[birthday][year]'   => '1970',
                    'frontend_user[pin]'              => '11111111H',
                    'frontend_user[credit_card]'      => '123456789012345',
                    'frontend_user[city]'             => '1',
                    'frontend_user[subscribed]'       => '1'
                )
            )
        );
    }
}
