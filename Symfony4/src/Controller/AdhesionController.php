<?php

namespace App\Controller;

use App\Entity\Pac;
use App\Form\PacType;
use App\Entity\Adherent;
use App\Entity\Exercice;
use App\Form\AdherentType;
use App\Service\ExcelReader;

use App\Entity\CompteCotisation;
use Symfony\Component\Form\FormError;
use App\Repository\AdherentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
     * @Route("/adhesion/beneficiaires/retires", name="adhesion_beneficiaires_retires")
     */
    public function beneficiairesRetires()
    {
        $pacs = $this->getDoctrine()
                         ->getRepository(Pac::class)
                         ->findPacRetirer();
        return $this->render('adhesion/beneficiairesRetires.html.twig', [
            'pacs' => $pacs
        ]);
    }

    /**
     * @Route("/adhesion/beneficiaires/retires/{id}", name="adhesion_beneficiaires_integre")
     */
    public function beneficiairesIntegrer(Pac $pac)
    {
        $manager = $this->getDoctrine()->getManager();
        $pac->setIsSortie(false);
        $pac->setRemarque(null);
        $pac->setDateSortie(null);

        $manager->persist($pac);
        $manager->flush();

        return $this->redirectToRoute('adhesion_show', ['id' => $pac->getAdherent()->getId()]);

    }

    /**
     * @Route("/adhesion/inscrire", name="adhesion_new")
     */
    public function create(Request $request)
    { 
        $adherent = new Adherent();
        $generatedNumero = $this->getDoctrine()
                         ->getRepository(Adherent::class)
                         ->generateNumero();
        $adherent->setNumero($generatedNumero);
        $formAdherent = $this->createForm(AdherentType::class, $adherent);
        $formAdherent->handleRequest($request);
        if ($formAdherent->isSubmitted() && $formAdherent->isValid()) {
            // Set the adherent photo
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

            // create the current compteCotisation
            $currentExercice = $this->getDoctrine()
                                    ->getRepository(Exercice::class)
                                    ->findCurrent();
            $newCompteCotisation = new CompteCotisation($currentExercice, $adherent);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($adherent);
            $manager->persist($newCompteCotisation);
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
            $manager = $this->getDoctrine()->getManager();
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
     * @Entity("adherent", expr="repository.findOneById(id)")
     */
    public function show(Adherent $adherent, AdherentRepository $adherentRepo)
    {
        $exercice = $this->getDoctrine()
                         ->getRepository(Exercice::class)
                         ->findCurrent();
        
        return $this->render('adhesion/show.html.twig', [
            'adherent' => $adherent,
            'exercice' => $exercice,
        ]);
    }

    /**
     * @Route("/adhesion/{id}/pac/{idPac}/retirer", name="adhesion_remove_pac")
     */
    public function retirerPac(Adherent $adherent, $idPac, Request $request)
    { 
        $manager = $this->getDoctrine()->getManager();
        $pac = $this->getDoctrine()
                    ->getRepository(Pac::class)
                    ->find($idPac);

        $formPac = $this->createFormBuilder($pac)
                        ->add('dateSortie', DateType::class,[
                            'data' => new \DateTime()
                        ])
                        ->add('remarque', TextareaType::class)
                        ->getForm();
        $formPac->handleRequest($request);
        if ($formPac->isSubmitted() && $formPac->isValid()) {
            $pac->setIsSortie(true);

            // update the compte cotisation / if nouveau ++
            $currentExercice = $this->getDoctrine()
                                    ->getRepository(Exercice::class)
                                    ->findCurrent();
            $currentCompteCotisation = $adherent->getCompteCotisation($currentExercice);
            // test if the pac is nouveau or ancien
            $isNouveau = $pac->isNouveau($currentExercice);
            if ($isNouveau) {
                $currentCompteCotisation->decrementNouveau();
            } else {
                $currentCompteCotisation->decrementAncien();
            }

            $manager->persist($adherent);
            $manager->persist($pac);
            $manager->persist($currentCompteCotisation);
            $manager->flush();

            return $this->redirectToRoute('adhesion_show', ['id' => $adherent->getId()]);
        }
        return $this->render('adhesion/removePac.html.twig', [
            'form' => $formPac->createView(),
            'adherent' => $adherent,
            'pac' => $pac
        ]);
    }

    /**
     * @Route("/adhesion/{id}/pac/xlsx", name="adhesion_add_pac_xlsx")
     */
    public function addPacFromExcel(Adherent $adherent, Request $request, ExcelReader $excelReader)
    {
        $form = $this->createFormBuilder()
                    ->add('file', FileType::class, [
                        'mapped' => false,
                        'required' => true,
                    ])
                    ->add('save', SubmitType::class, ['label' => 'Importer xlsx'])
                    ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // read data from Excel file
            $xlsxFile = $form->get('file')->getData();
            if ($xlsxFile) {
                $output = $excelReader->savePacFromExcel($adherent, $xlsxFile);  
                if ($output['hasError'] === false) {
                    return $this->redirectToRoute('adhesion_show', ['id' => $adherent->getId()]); 
                } else {
                    foreach ($output['ErrorMessages'] as $message) {
                        $form->get('file')->addError(new FormError($message));
                    }
                }             
            }
            
        }
        
        return $this->render('adhesion/addPacXlsx.html.twig', [
            'form' => $form->createView(),
            'adherent' => $adherent,
        ]);
    }

    /**
     * @Route("/adhesion/{id}/pac", name="adhesion_add_pac")
     */
    public function addPac(Adherent $adherent, Request $request)
    { 
        $pac = new Pac();
        $generatedCode = $this->getDoctrine()
                         ->getRepository(Pac::class)
                         ->generateCode($adherent);
        $pac->setCodeMutuelle($generatedCode);

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
            } else {
                $pac->setPhoto('assets/images/users/profile.png');
            }

            $pac->setCreatedAt(new \DateTime());
            $pac->setIsSortie(false);

            // update the compte cotisation / if nouveau ++
            $currentExercice = $this->getDoctrine()
                                    ->getRepository(Exercice::class)
                                    ->findCurrent();
            $currentCompteCotisation = $adherent->getCompteCotisation($currentExercice);
            // test if the pac is nouveau or ancien
            $isNouveau = $pac->isNouveau($currentExercice);
            if ($isNouveau) {
                $currentCompteCotisation->incrementNouveau();
            } else {
                $currentCompteCotisation->incrementAncien();
            }

            $adherent->addPac($pac);
            
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($adherent);
            $manager->persist($pac);
            $manager->persist($currentCompteCotisation);
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
}
