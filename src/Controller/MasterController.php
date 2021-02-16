<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Master;



class MasterController extends AbstractController
{
    /**
     * @Route("/master", name="master_index", methods={"GET"})
     */
    public function index(Request $r): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 

        $masters = $this->getDoctrine()
        ->getRepository(Master::class);

        if ('name_az' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $masters = $masters->findBy([],['name'=>'asc']);
        }

        elseif ('name_za' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $masters = $masters->findBy([],['name'=>'desc']);
        }

        elseif ('surname_az' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $masters = $masters->findBy([],['surname'=>'asc']);
        }

        elseif ('surname_za' == $r->query->get('sort')) {
            // tinka visu kriteriju autoriai, name pagal abc
            $masters = $masters->findBy([],['surname'=>'desc']);
        }
        
        else {
            $masters = $masters->findAll();
        }
        

        return $this->render('master/index.html.twig', [
            'controller_name' => 'MasterController',
            'masters' => $masters,
            'sortBy' => $r->query->get('sort') ?? 'default',
            'success' => $r->getSession()->getFlashBag()->get('success', []),
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
        ]);
    }

    /**
     * @Route("/master/create", name="master_create", methods={"GET"})
     */
    public function create(Request $r): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 

        $master_name = $r->getSession()->getFlashBag()->get('master_name', []);
        $master_surname = $r->getSession()->getFlashBag()->get('master_surname', []);


        return $this->render('master/create.html.twig', [
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'master_name' => $master_name[0] ?? '',
            'master_surname' => $master_surname[0] ?? '',
        ]);
    }

    /**
     * @Route("/master/store", name="master_store", methods={"POST"})
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {
        $master = new Master;

        $master->
        setName($r->request->get('master_name'))->
        setSurname($r->request->get('master_surname'));

        $errors = $validator->validate($master);

        // jei yra error, verciame i string ir ji graziname, parodo error'a
        if (count($errors) > 0) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            // klaidos atveju ivestas vardas ir pavarde lieka
            $r->getSession()->getFlashBag()->add('master_name', $r->request->get('master_name'));
            $r->getSession()->getFlashBag()->add('master_surname', $r->request->get('master_surname')); 
            
            return $this->redirectToRoute('master_create');
        
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($master);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Master sekmingai pridetas.');

        return $this->redirectToRoute('master_index');
    }

    /**
     * @Route("/master/edit/{id}", name="master_edit", methods={"GET"})
     */
    public function edit(Request $r, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 
        
        $master = $this->getDoctrine()
        ->getRepository(Master::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        $master_name = $r->getSession()->getFlashBag()->get('master_name', []);
        $master_surname = $r->getSession()->getFlashBag()->get('master_surname', []);

        return $this->render('master/edit.html.twig', [
            'master' => $master, // perduodame
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'success' => $r->getSession()->getFlashBag()->get('success', []),
            'master_name' => $master_name[0] ?? '',
            'master_surname' => $master_surname[0] ?? '',
        ]);
    }

    /**
     * @Route("/master/update/{id}", name="master_update", methods={"POST"})
     */
    public function update(Request $r, ValidatorInterface $validator, $id): Response
    {
        $master = $this->getDoctrine()
        ->getRepository(Master::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        $master->
        setName($r->request->get('master_name'))->
        setSurname($r->request->get('master_surname'));

        $errors = $validator->validate($master);

        // jei yra error, verciame i string ir ji graziname, parodo error'a
        if (count($errors) > 0) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            // klaidos atveju ivestas vardas ir pavarde lieka
            $r->getSession()->getFlashBag()->add('master_name', $r->request->get('master_name'));
            $r->getSession()->getFlashBag()->add('master_surname', $r->request->get('master_surname')); 
            
            return $this->redirectToRoute('master_edit',['id'=>$master->getId()]);
        
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($master);
        $entityManager->flush();



        $r->getSession()->getFlashBag()->add('success', 'Master ' .$master->getName().' '.$master->getSurname().' sekimgai pakeistas');

        return $this->redirectToRoute('master_index');
    }

    /**
     * @Route("/master/delete/{id}", name="master_delete", methods={"POST"})
     */
    public function delete(Request $r, $id): Response
    {
        $master = $this->getDoctrine()
        ->getRepository(Master::class)
        ->find($id); // randame butent ta autoriu, kurio id perduodamas

        // remove metodu padauodame ta autoriu ir vykdome
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($master);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Master ' .$master->getName().' '.$master->getSurname().' sekimgai istrintas');

        return $this->redirectToRoute('master_index');
    }
}
