<?php

namespace App\Controller;

use App\Entity\Pac;
use App\Entity\Tier;
use App\Form\PacType;
use App\Entity\Adherent;
use App\Entity\Exercice;
use App\Form\AdherentType;

use App\Service\ExcelReader;
use App\Entity\CompteCotisation;
use App\Repository\PacRepository;
use App\Repository\CompteRepository;
use Symfony\Component\Form\FormError;
use App\Repository\AdherentRepository;
use App\Repository\ExerciceRepository;
use App\Repository\ParametreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CompteCotisationRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class AdhesionController extends AbstractController
{
    /**
     * @Route("/adhesion", name="adhesion")
     */
    public function index(AdherentRepository $repository)
    {
        return $this->render('adhesion/index.html.twig', [
            'adherents' => $repository->findAll()
        ]);
    }

    /**
     * @Route("/adhesion/beneficiaires/retires", name="adhesion_beneficiaires_retires")
     */
    public function beneficiairesRetires(PacRepository $repository)
    {
        return $this->render('adhesion/beneficiairesRetires.html.twig', [
            'pacs' => $repository->findPacRetirer()
        ]);
    }

    /**
     * @Route("/adhesion/beneficiaires/retires/{id}", name="adhesion_beneficiaires_integre")
     */
    public function beneficiairesIntegrer(Pac $pac, EntityManagerInterface $manager)
    {
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
    public function create(Request $request, CompteRepository $repoCompte, AdherentRepository $repository, ParametreRepository $paramRepo, EntityManagerInterface $manager)
    { 
        $adherent = new Adherent();
        $generatedNumero = $repository->generateNumero();
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
                $adherent->setPhoto('/assets/images/home.png');
            }

            $adherent->setCreatedAt(new \DateTime());

            // create the current compteCotisation
            $currentExercice = $this->getDoctrine()
                                    ->getRepository(Exercice::class)
                                    ->findCurrent();
            $newCompteCotisation = new CompteCotisation($currentExercice, $adherent);          
            // Create analytic account           
            $codeAnalytique = $paramRepo->findOneByNom('code_analytique_cong')->getValue(); // Le modele code analytique
            $code = str_ireplace('{n}', $adherent->getNumero(), $codeAnalytique);

            $compteAssocie = $paramRepo->findOneByNom('compte_dette_prestation')->getValue(); 
            $compteCollectif = $repoCompte->findOneBy(['poste'=>$compteAssocie]);

            $tier = new Tier();
            $tier->setCode($code);
            $tier->setType('F');
            $tier->setLibelle($adherent->getNom());
            $tier->setAdresse($adherent->getAdresse());
            $tier->setContact($adherent->getTelephone1());
            $tier->setCompte($compteCollectif);

            $adherent->setTier($tier);

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
     * @Route("/adhesion/rapport", name="adhesion_rapport")
     * Rapport sur les adhérés
     */
    public function rapportAdhesion(AdherentRepository $repo)
    {
        return $this->render('adhesion/rapportAdhesion.html.twig', [
            'adherents' => $repo->findAll()
        ]);       
    }

    /**
     * @Route("/adhesion/{id}/edit", name="adhesion_edit")
     */
    public function edit(Adherent $adherent = null, Request $request, EntityManagerInterface $manager)
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
    public function show(Adherent $adherent, CompteCotisationRepository $repository, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $compteCotisation = $repository->findCompteCotisation($adherent, $exercice);
        return $this->render('adhesion/show.html.twig', [
            'adherent' => $adherent,
            'exercice' => $exercice,
            'compteCotisation' => $compteCotisation
        ]);
    }

    /**
     * @Route("/adhesion/{id}/pac/{idPac}/retirer", name="adhesion_remove_pac")
     * @ParamConverter("adherent", options={"mapping": {"id":"id"}})
     * @ParamConverter("pac", options={"mapping":{"idPac": "id"}})
     */
    public function retirerPac(Adherent $adherent, Pac $pac, Request $request, EntityManagerInterface $manager)
    { 
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
    public function addPac(Adherent $adherent, PacRepository $repository, Request $request, EntityManagerInterface $manager)
    { 
        $pac = new Pac();
        $generatedCode = $repository->generateCode($adherent);
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
     * @ParamConverter("adherent", options={"mapping": {"id":"id"}})
     * @ParamConverter("pac", options={"mapping":{"idPac": "id"}})
     */
    public function editPac(Adherent $adherent, Pac $pac, Request $request, EntityManagerInterface $manager)
    { 
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
