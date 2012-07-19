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
use Coupon\StoreBundle\Entity\Store;
use Coupon\BackendBundle\Form\StoreType;

/**
 * Store controller.
 *
 */
class StoreController extends Controller
{
    /**
     * Lists all Store entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $slug = $this->getRequest()->getSession()->get('city');
        $entities = $em->getRepository('CityBundle:City')->findAllStores($slug);

        return $this->render('BackendBundle:Store:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Store entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('StoreBundle:Store')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('The requested store is unavailable.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Store:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Store entity.
     *
     */
    public function newAction()
    {
        $entity = new Store();
        $form   = $this->createForm(new StoreType(), $entity);

        return $this->render('BackendBundle:Store:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Store entity.
     *
     */
    public function createAction()
    {
        $entity  = new Store();
        $request = $this->getRequest();
        $form    = $this->createForm(new StoreType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_store_show', array('id' => $entity->getId())));

        }

        return $this->render('BackendBundle:Store:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Store entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('StoreBundle:Store')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('The requested store is unavailable.');
        }

        $editForm = $this->createForm(new StoreType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Store:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Store entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('StoreBundle:Store')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('The requested store is unavailable.');
        }

        $editForm   = $this->createForm(new StoreType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_store_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:Store:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Store entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('StoreBundle:Store')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('The requested store is unavailable.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('backend_store'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
