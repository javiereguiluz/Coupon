<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Coupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coupon\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Coupon\CityBundle\Entity\City;
use Coupon\BackendBundle\Form\CityType;

/**
 * City controller.
 *
 */
class CityController extends Controller
{
    /**
     * Lists all City entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('CityBundle:City')->findAll();

        return $this->render('BackendBundle:City:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a City entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CityBundle:City')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('The requested city is unavailable.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:City:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new City entity.
     *
     */
    public function newAction()
    {
        $entity = new City();
        $form   = $this->createForm(new CityType(), $entity);

        return $this->render('BackendBundle:City:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new City entity.
     *
     */
    public function createAction()
    {
        $entity  = new City();
        $request = $this->getRequest();
        $form    = $this->createForm(new CityType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_city_show', array('id' => $entity->getId())));

        }

        return $this->render('BackendBundle:City:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing City entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CityBundle:City')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('The requested city is unavailable.');
        }

        $editForm = $this->createForm(new CityType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:City:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing City entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('CityBundle:City')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('The requested city is unavailable.');
        }

        $editForm   = $this->createForm(new CityType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_city_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:City:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a City entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('CityBundle:City')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('The requested city is unavailable.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('backend_city'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
