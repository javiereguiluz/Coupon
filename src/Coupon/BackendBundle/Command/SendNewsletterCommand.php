<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\BackendBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * This command sends a daily newsletter to subscribed users with the offer
 * that will be published the next day on their associated cities.
 */
class SendNewsletterCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('send:newsletter')
            ->setDefinition(array(
                new InputOption('action', false, InputOption::VALUE_OPTIONAL, 'Allows to just create the emails instead of sending them')
            ))
            ->setDescription('Creates and sends the daily newsletter email')
            ->setHelp(<<<EOT
<info>send:newsletter</info> command creates and sends by email the daily
newsletter to subscribed users.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = 'dev' == $input->getOption('env') ? 'http://coupon.local' : 'http://coupon.com';
        $action = $input->getOption('action');

        $container = $this->getContainer();
        $em = $container->get('doctrine')->getEntityManager();

        // Get the list of subscribed users
        $users = $em->getRepository('UserBundle:User')->findBy(array('subscribed' => true));

        $output->writeln(sprintf(' <info>%s</info> emails are going to be sent', count($users)));

        // Look for tomorrow's "Daily Offer" in each city
        $offers = array();
        $cities = $em->getRepository('CityBundle:City')->findAll();
        foreach ($cities as $city) {
            $id   = $city->getId();
            $slug = $city->getSlug();

            $offers[$id] = $em->getRepository('OfferBundle:Offer')->findTomorrowOffer($slug);
        }

        // Create the email for each user
        foreach ($users as $user) {
            $city = $user->getCity();
            $offer = $offers[$city->getId()];

            $content = $container->get('twig')->render(
                'BackendBundle:Offer:email.html.twig',
                array('host' => $host, 'city' => $city, 'offer' => $offer, 'user' => $user)
            );

            // Send the email
            if ('send' == $action) {
                $email = \Swift_Message::newInstance()
                    ->setSubject($offer->getName().' at '.$offer->getStore()->getName())
                    ->setFrom(array('daily-offer@coupon.com' => 'Coupon - Daily Offer'))
                    ->setTo($user->getEmail())
                    ->setBody($content, 'text/html')
                ;
                $this->getContainer()->get('mailer')->send($email);
            }
        }

        if ('send' != $action) {
            $output->writeln("\n No email was sent. Execute the following command,\n to send the emails:\n <info>./app/console send:newsletter --action=send</info>\n");
        }
    }
}
