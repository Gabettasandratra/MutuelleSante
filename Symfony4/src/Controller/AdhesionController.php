<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
        $adhrents = $this->getDoctrine()
                         ->getRepository(Adherent::class)
                         ->findAll();
        return $this->render('adhesion/index.html.twig', [
            'adherents' => $adhrents
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
            $pac->setAdherent($adherent);
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
}
