<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\OfferBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * This functional test checks the website homepage, the process of purchasing
 * an offer and also checks the application performance.
 */
class DefaultControllerTest extends WebTestCase
{
    /** @test */
    public function homepageRedirectsToACity()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertEquals(302, $client->getResponse()->getStatusCode(),
            'Homepage without parameters redirects to some city homepage (status 302)'
        );
    }

    /** @test */
    public function homepageDisplaysOneActiveOffer()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $crawler = $client->followRedirect();
        
        $activeOffers = $crawler->filter(
            'article.offer section.description a:contains("Buy now")'
        )->count();

        $this->assertEquals(1, $activeOffers,
            'Homepage shows just one active offer'
        );
    }

    /** @test */
    public function usersCanRegisterFromTheHomepage()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $crawler = $client->followRedirect();

        $registerLinks = $crawler->filter('html:contains("Sign up now")')->count();

        $this->assertGreaterThan(0, $registerLinks,
            'Homepage contains at least one link to registration form'
        );
    }

    /** @test */
    public function defaultCityIsSelectedForAnonymousUsers()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $crawler = $client->followRedirect();

        $defaultCity = $client->getContainer()->getParameter('coupon.default_city');
        $homepageCity = $crawler->filter('header nav select option[selected="selected"]')->attr('value');

        $this->assertEquals($defaultCity, $homepageCity,
            'The homepage for anonoymous users selects the default city'
        );
    }

    /** @test */
    public function anonymousUsersCannotBuyOffers()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $crawler = $client->followRedirect();

        $purchaseLink = $crawler->selectLink('Buy now')->link();
        $client->click($purchaseLink);

        $this->assertTrue($client->getResponse()->isRedirect(),
            'When an anonymous user tries to purchase an offer, it gets redirected to another page'
        );
    }

    /** @test */
    public function losUsersAnonimosDebenLoguearseParaPoderComprar()
    {
        $loginPath = '/.*\/user\/login_check/';
        $client = static::createClient();
        $client->request('GET', '/');
        $crawler = $client->followRedirect();

        $purchaseLink = $crawler->selectLink('Buy now')->link();
        $client->click($purchaseLink);
        $crawler = $client->followRedirect();

        $this->assertRegExp($loginPath, $crawler->filter('article form')->attr('action'),
            'When an anonymous user tries to purchase an offer, the application displays the login form'
        );
    }

    /** @test */
    public function homepageRequiresFewDatabaseQueries()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        if ($profiler = $client->getProfile()) {
            $this->assertLessThan(4, count($profiler->getCollector('db')->getQueries()),
                'Homepage generation requires less than 4 database queries'
            );
        }
    }

    /** @test */
    public function homepageIsRenderedFast()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        if ($profiler = $client->getProfile()) {
            $this->assertLessThan(0.5, $profiler->getCollector('timer')->getTime(),
                'Homepage is rendered in less than 500 miliseconds'
            );
        }
    }
}
