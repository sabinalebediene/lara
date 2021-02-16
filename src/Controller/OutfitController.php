<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Master;
use App\Entity\Outfit;

class OutfitController extends AbstractController
{
    /**
     * @Route("/outfit", name="outfit_index")
     */
    public function index(Request $r): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 

        $masters = $this->getDoctrine()
        ->getRepository(Master::class)
        ->findBy([],['name'=>'asc']);

        $outfits = $this->getDoctrine()
        ->getRepository(Outfit::class);

        if (null !== $r->query->get('master_id')) {
            $outfits = $outfits->findBy(['master_id' => $r->query->get('master_id')]);
        }

        else {
            $outfits = $outfits->findAll();
        }

        return $this->render('outfit/index.html.twig', [
            'controller_name' => 'OutfitController',
            'masterId' => $r->query->get('master_id') ?? 0,
            'outfits' => $outfits,
            'masters' => $masters,
            'success' => $r->getSession()->getFlashBag()->get('success', [])
        ]);
    }

    /**
     * @Route("/outfit/create", name="outfit_create", methods={"GET"})
     */
    public function create(Request $r): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 

        $masters = $this->getDoctrine()
        ->getRepository(Master::class)
        ->findBy([],['name'=>'asc']);


        $outfit_type = $r->getSession()->getFlashBag()->get('outfit_type', []);

        return $this->render('outfit/create.html.twig', [
            'masters' => $masters,
            'outfit_type' => $outfit_type[0] ?? '',
            'outfit_master_id' => $outfit_master_id[0] ?? '',
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    /**
     * @Route("/outfit/store", name="outfit_store", methods={"POST"})
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {
        $master = $this->getDoctrine()
        ->getRepository(Master::class)
        ->find($r->request->get('outfits_master'));


        // autoriau validacija, jei jis nepaselectintas
        if(null === $master) {
            $r->getSession()->getFlashBag()->add('errors', 'Pasirink masteri');
        }

        $outfit = new Outfit;
        $outfit
        ->setType($r->request->get('outfit_type'))
        ->setColor($r->request->get('outfit_color'))
        ->setSize((int)$r->request->get('outfit_size'))
        ->setAbout($r->request->get('outfit_about'))
        ->setMaster($master);

        $errors = $validator->validate($outfit);

        // jei yra error, verciame i string ir ji graziname, parodo error'a
        if (count($errors) > 0 || null === $master) {

            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            $r->getSession()->getFlashBag()->add('outfit_type', $r->request->get('outfit_type'));
            $r->getSession()->getFlashBag()->add('outfit_master_id', $r->request->get('outfit_master_id'));

            return $this->redirectToRoute('outfit_create');
            
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($outfit);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Outfit '.$outfit->getType().' for '.$outfit->getMaster()->getName().'  '.$outfit->getMaster()->getSurname().' was created.');

        return $this->redirectToRoute('outfit_index');
    }

    /**
     * @Route("/outfit/edit/{id}", name="outfit_edit", methods={"GET"})
     */
    public function edit(Request $r, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); 
        
        $outfit = $this->getDoctrine()
        ->getRepository(Outfit::class)
        ->find($id);

        $masters = $this->getDoctrine()
        ->getRepository(Master::class)
        ->findBy([],['surname'=>'asc']);

    
        return $this->render('outfit/edit.html.twig', [
            'outfit' => $outfit,
            'masters' => $masters,
            'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'success' => $r->getSession()->getFlashBag()->get('success', [])
        ]);
    }

    /**
     * @Route("/outfit/update/{id}", name="outfit_update", methods={"POST"})
     */
    public function update(Request $r, $id, ValidatorInterface $validator): Response
    {
        $master = $this->getDoctrine()
        ->getRepository(Master::class)
        ->find($r->request->get('outfit_master'));

        $outfit = $this->getDoctrine()
        ->getRepository(Outfit::class)
        ->find($id);
    
        $outfit
        ->setType($r->request->get('outfit_type'))
        ->setColor($r->request->get('outfit_color'))
        ->setSize($r->request->get('outfit_size'))
        ->setAbout($r->request->get('outfit_about'))
        ->setMaster($master);

        $errors = $validator->validate($outfit);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            return $this->redirectToRoute('grade_edit', ['id'=>$outfit->getId()]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($outfit);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Grade '.$outfit->getType().' for '.$outfit->getMaster()->getName().' '.$outfit->getMaster()->getSurname().' was updated.');

        return $this->redirectToRoute('outfit_index');
    }

    /**
     * @Route("/outfit/delete/{id}", name="outfit_delete", methods={"POST"})
     */
    public function delete(Request $r, $id): Response
    {

        $outfit = $this->getDoctrine()
        ->getRepository(Outfit::class)
        ->find($id);
    

        // remove metodu padauodame ta autoriu ir vykdome
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($outfit);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'Outfit '.$outfit->getType().' for '.$outfit->getMaster()->getName().' '.$outfit->getMaster()->getSurname().' was deleted.');

        return $this->redirectToRoute('outfit_index');
    }
}
