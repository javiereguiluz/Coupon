<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\OfferBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SitioController extends Controller
{
    /**
     * Renders the static pages of the website
     *
     * @param string $page The slug of the page
     */
    public function staticAction($page)
    {
        $response = $this->render('OfferBundle:Site:'.$page.'.html.twig');
        $response->setSharedMaxAge(3600 * 24);
        $response->setPublic();

        return $response;

        /* Use the following code to display a 404 Not Found error for pages that don't exist.

        $template = realpath(__DIR__.'/../Resources/views/Site/'.$page.'.html.twig');

        if (file_exists($template)) {
            $response = $this->render('OfferBundle:Site:'.$page.'.html.twig');
            $response->setSharedMaxAge(3600 * 24);
            $response->setPublic();

            return $response;
        } else {
            throw $this->createNotFoundException('The requested page is unavailable');
        }
        */
    }

    /**
     * Renders the contact form and processes it to send the messages by email
     */
    public function contactAction()
    {
        $request = $this->getRequest();

        $form = $this->createFormBuilder()
            ->add('sender', 'email')
            ->add('message', 'textarea')
            ->getForm()
        ;

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $content = sprintf(" Sender: %s \n\n Message: %s \n\n Browser: %s \n IP address: %s \n",
                    $data['sender'],
                    htmlspecialchars($data['sender']),
                    $request->server->get('HTTP_USER_AGENT'),
                    $request->server->get('REMOTE_ADDR')
                );

                $email = \Swift_Message::newInstance()
                    ->setSubject('Contact')
                    ->setFrom($data['sender'])
                    ->setTo('contact@coupon')
                    ->setBody($content)
                ;

                $this->container->get('mailer')->send($email);

                $this->get('session')->setFlash('info',
                    'Your message was sent successfully.'
                );

                return $this->redirect($this->generateUrl('homepage'));
            }
        }

        return $this->render('OfferBundle:Site:contact.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
