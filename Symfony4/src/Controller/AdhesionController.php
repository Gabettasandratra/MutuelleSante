<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use App\Entity\Adherent;
use App\Form\AdherentType;
use App\Entity\Pac;
use App\Form\PacType;

class AdhesionController extends AbstractController
{
    /**
     * @Route("/adhesion", name="adhesion")
     */
    public function index()
    {
        $adherents = $this->getDoctrine()
                         ->getRepository(Adherent::class)
                         ->findAll();
        return $this->render('adhesion/index.html.twig', [
            'adherents' => $adherents
        ]);
    }

    /**
     * @Route("/adhesion/beneficiaires", name="adhesion_beneficiaires")
     */
    public function beneficiaires()
    {
        $adherents = $this->getDoctrine()
                         ->getRepository(Adherent::class)
                         ->findAll();
        return $this->render('adhesion/beneficiaires.html.twig', [
            'adherents' => $adherents
        ]);
    }

    /**
     * @Route("/adhesion/inscrire", name="adhesion_new")
     */
    public function create(Request $request)
    { 
        $manager = $this->getDoctrine()->getManager();
        $adherent = new Adherent();

        $generatedCode = $this->getDoctrine()
                         ->getRepository(Adherent::class)
                         ->generateCode();
        $adherent->setCodeMutuelle($generatedCode);

        $formAdherent = $this->createForm(AdherentType::class, $adherent);
        $formAdherent->handleRequest($request);
        if ($formAdherent->isSubmitted() && $formAdherent->isValid()) {
            $photoFile = $formAdherent->get('photo')->getData();
            
            if ($photoFile) {
                $fileName = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('users_img_root_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $adherent->setPhoto($this->getParameter('users_img_directory').'/'.$fileName);
            } else {
                $adherent->setPhoto('http://placehold.it/100x100');
            }
            $m = date('m', $adherent->getDateInscription()->getTimestamp());
            $adherent->setTailleFamille(array_fill($m-1, 13-$m, 1));
            $adherent->setCreatedAt(new \DateTime());
            $manager->persist($adherent);
            $manager->flush();

            return $this->redirectToRoute('adhesion_show', ['id' => $adherent->getId()]);
        }
        return $this->render('adhesion/form.html.twig', [
            'form' => $formAdherent->createView(),
        ]);
    }

    /**
     * @Route("/adhesion/{id}/edit", name="adhesion_edit")
     */
    public function edit(Adherent $adherent = null, Request $request)
    { 
        $manager = $this->getDoctrine()->getManager();
        if ($adherent === null) {
            return $this->redirectToRoute('adhesion_new');
        }
        $formAdherent = $this->createForm(AdherentType::class, $adherent);
        $formAdherent->handleRequest($request);
        if ($formAdherent->isSubmitted() && $formAdherent->isValid()) {
            $photoFile = $formAdherent->get('photo')->getData();           
            if ($photoFile) {
                $fileName = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('users_img_root_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $adherent->setPhoto($this->getParameter('users_img_directory').'/'.$fileName);
            }
            $manager->persist($adherent);
            $manager->flush();

            return $this->redirectToRoute('adhesion_show', ['id' => $adherent->getId()]);
        }
        return $this->render('adhesion/edit.html.twig', [
            'form' => $formAdherent->createView(),
            'adherent' => $adherent
        ]);
    }

    /**
     * @Route("/adhesion/{id}", name="adhesion_show")
     */
    public function show(Adherent $adherent)
    {
        return $this->render('adhesion/show.html.twig', [
            'adherent' => $adherent
        ]);
    }

    /**
     * @Route("/adhesion/{id}/pac", name="adhesion_add_pac")
     */
    public function addPac(Adherent $adherent, Request $request)
    { 
        $manager = $this->getDoctrine()->getManager();
        $pac = new Pac();
        $generatedCode = $this->getDoctrine()
                         ->getRepository(Pac::class)
                         ->generateCode($adherent);
        $pac->setCodeMutuelle($generatedCode);

        $formPac = $this->createForm(PacType::class, $pac);
        $formPac->handleRequest($request);
        if ($formPac->isSubmitted() && $formPac->isValid()) {
            $pac->setCreatedAt(new \DateTime());
            $pac->setIsSortie(false);
            $pac->setAdherent($adherent);
            $photoFile = $formPac->get('photo')->getData();           
            if ($photoFile) {
                $fileName = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('users_img_root_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $pac->setPhoto($this->getParameter('users_img_directory').'/'.$fileName);
            }
            // Update taille famille from the current month
            $m = date('m', $pac->getDateEntrer()->getTimestamp());
            $tF = $adherent->getTailleFamille();
            foreach ($tF as $key => $value) {
                if ($key >= $m-1) {
                    $tF[$key] = $value + 1; 
                }
            }
            $adherent->setTailleFamille($tF);
            $manager->persist($adherent);
            $manager->persist($pac);
            $manager->flush();

            return $this->redirectToRoute('adhesion_show', ['id' => $adherent->getId()]);
        }
        return $this->render('adhesion/addPac.html.twig', [
            'form' => $formPac->createView(),
            'adherent' => $adherent
        ]);
    }

    /**
     * @Route("/adhesion/{id}/pac/{idPac}", name="adhesion_edit_pac")
     */
    public function editPac(Adherent $adherent, $idPac, Request $request)
    { 
        $manager = $this->getDoctrine()->getManager();
        $pac = $this->getDoctrine()
                         ->getRepository(Pac::class)
                         ->find($idPac);

        $formPac = $this->createForm(PacType::class, $pac);
        $formPac->handleRequest($request);
        if ($formPac->isSubmitted() && $formPac->isValid()) {
            $photoFile = $formPac->get('photo')->getData();           
            if ($photoFile) {
                $fileName = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('users_img_root_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $pac->setPhoto($this->getParameter('users_img_directory').'/'.$fileName);
            }
            $manager->persist($pac);
            $manager->flush();

            return $this->redirectToRoute('adhesion_show', ['id' => $adherent->getId()]);
        }
        return $this->render('adhesion/addPac.html.twig', [
            'form' => $formPac->createView(),
            'adherent' => $adherent,
            'pac' => $pac,
            'editMode' => true,
        ]);
    } 

    /**
     * @Route("/adhesion/{id}/pac/{idPac}/remove", name="adhesion_remove_pac")
     */
    public function removePac(Adherent $adherent, $idPac, Request $request)
    { 
        $manager = $this->getDoctrine()->getManager();
        $pac = $this->getDoctrine()
                    ->getRepository(Pac::class)
                    ->find($idPac);

        $formPac = $this->createFormBuilder($pac)
                        ->add('dateSortie', DateType::class)
                        ->add('remarque', TextareaType::class)
                        ->getForm();
        $formPac->handleRequest($request);
        if ($formPac->isSubmitted() && $formPac->isValid()) {
            $pac->setIsSortie(true);
            // Update taille famille from the current month
            $m = date('m', $pac->getDateSorite()->getTimestamp());
            $tF = $adherent->getTailleFamille();
            foreach ($tF as $key => $value) {
                if ($key >= $m-1) {
                    $tF[$key] = $value - 1; 
                }
            }
            $adherent->setTailleFamille($tF);
            $manager->persist($pac);
            $manager->flush();

            return $this->redirectToRoute('adhesion_show', ['id' => $adherent->getId()]);
        }
        return $this->render('adhesion/removePac.html.twig', [
            'form' => $formPac->createView(),
            'adherent' => $adherent,
            'pac' => $pac
        ]);
    }
}
