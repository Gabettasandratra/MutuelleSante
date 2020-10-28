<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Compte;
use App\Form\UserType;
use App\Entity\Exercice;
use App\Entity\Parametre;
use App\Form\ExerciceType;
use App\Form\ParametersType;
use App\Service\ExerciceService;
use App\Service\ParametreService;
use App\Repository\UserRepository;
use App\Repository\CompteRepository;
use Symfony\Component\Form\FormError;
use App\Repository\ExerciceRepository;
use App\Repository\ParametreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ParametreController extends AbstractController
{
    /**
     * @Route("/parametre/mutuelle/donees", name="parametre_mutuelle_donnees")
     */
    public function donneesMutuelles(Request $request,ParametreRepository $repository,ParametreService $paramService,EntityManagerInterface $manager)
    {
        $parameters = $repository->getParameters();
        if (!$parameters) {
            $paramService->initialize();
            $arameters = $repository->getParameters();
        }

        $nom_mutuelle = $parameters['nom_mutuelle'];
        $adresse_mutuelle = $parameters['adresse_mutuelle'];
        $contact_mutuelle = $parameters['contact_mutuelle'];
        $email_mutuelle = $parameters['email_mutuelle'];

        $form = $this->createFormBuilder()
                ->add('nom_mutuelle', TextType::class, ['data'=>$nom_mutuelle->getValue()])
                ->add('adresse_mutuelle', TextType::class, ['data'=>$adresse_mutuelle->getValue()])
                ->add('contact_mutuelle', TextType::class, ['data'=>$contact_mutuelle->getValue()])
                ->add('email_mutuelle', TextType::class, ['data'=>$email_mutuelle->getValue()])
                ->getForm(); 
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $nom_mutuelle->setValue($form->get('nom_mutuelle')->getData());
            $adresse_mutuelle->setValue($form->get('adresse_mutuelle')->getData());
            $contact_mutuelle->setValue($form->get('contact_mutuelle')->getData());
            $email_mutuelle->setValue($form->get('email_mutuelle')->getData());
            $manager->flush();
        }
        return $this->render('parametre/donnees.html.twig', [
            'form' => $form->createView()
        ]); 
    }

    /**
     * @Route("/parametre/mutuelle/fonctions", name="parametre_mutuelle_fonctions")
     */
    public function parametreCotisationPrestation(ParametreRepository $repository,CompteRepository $repositoryCompte, ParametreService $paramService, Request $request, EntityManagerInterface $manager)
    {
        $allParameters = $repository->getParameters();
        if (!$allParameters) {
            $paramService->initialize();
            $allParameters = $repository->getParameters();
        }

        $pCotisation = $allParameters['compte_cotisation'];
        $pLabelCotisation = $allParameters['label_cotisation'];
        $pPeriodeCotisation = $allParameters['periode_cotisation_mois'];
        $pPrestation = $allParameters['compte_prestation'];
        $pLabelPrestation = $allParameters['label_prestation'];
        $pSoins = $allParameters['soins_prestation'];
        $pPercent = $allParameters['percent_prestation'];
        $pPercentRemb = $allParameters['percent_rembourse_prestation'];
        $pCompteRembPrestation = $allParameters['compte_dette_prestation'];
        $pPlafond = $allParameters['plafond_prestation'];

        /* Pour bien afficher le formulaire avec les données */
        $compteCot = $repositoryCompte->findOneByPoste($pCotisation->getValue());        
        $comptePre = $repositoryCompte->findOneByPoste($pPrestation->getValue());        
        $compteRembPre = $repositoryCompte->findOneByPoste($pCompteRembPrestation->getValue());        
        $data['compte_cotisation'] = $compteCot;
        $data['label_cotisation'] = $pLabelCotisation->getValue();
        $data['periode_cotisation_mois'] = $pPeriodeCotisation->getValue();
        $data['compte_prestation'] = $comptePre;
        $data['label_prestation'] = $pLabelPrestation->getValue();
        $data['percent_prestation'] = $pPercent->getValue();
        $data['percent_rembourse_prestation'] = $pPercentRemb->getValue();
        $data['compte_dette_prestation'] = $pCompteRembPrestation->getValue();
        $data['plafond_prestation'] = $pPlafond->getValue();
        $data['soins_prestation'] = json_encode($pSoins->getList());
        /* Le data est uniquement pour afficher le données dans le formulaire */

        $form = $this->createForm(ParametersType::class, $data);        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* Enregistrer tous les parametres données */
            $pCotisation->setValue($form->get('compte_cotisation')->getData()->getPoste());
            $pLabelCotisation->setValue($form->get('label_cotisation')->getData());
            $pPeriodeCotisation->setValue($form->get('periode_cotisation_mois')->getData());

            $pPrestation->setValue($form->get('compte_prestation')->getData()->getPoste());
            $pLabelPrestation->setValue($form->get('label_prestation')->getData());
            $pPercent->setValue($form->get('percent_prestation')->getData());
            $pPercentRemb->setValue($form->get('percent_rembourse_prestation')->getData());
            $pPlafond->setValue($form->get('plafond_prestation')->getData());
            $pCompteRembPrestation->setValue($form->get('compte_dette_prestation')->getData()->getPoste());
            $pSoins->setList(json_decode($form->get('soins_prestation')->getData(), true));

            $manager->flush(); // flush suffit
        }

        return $this->render('parametre/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/parametre/exercice", name="parametre_exercice")
     */
    public function exercice(ExerciceRepository $repository)
    {        
        return $this->render('parametre/exercice.html.twig', [
            'exercices' => $repository->findAll()
        ]);
    }

    /**
     * @Route("/parametre/exercice/configurer", name="parametre_exercice_configurer")
     */
    public function addExercice(ExerciceRepository $repository, Request $request, ExerciceService $exerciceService, SessionInterface $session)
    {      
        $exercice = new Exercice();  
        
        $dateDernier = $repository->findFinExercice();        
        if ($dateDernier) {
            $exercice->setAnnee((int)$dateDernier->format('Y') + 1);
            $exercice->setDateDebut($dateDernier->add(new \DateInterval('P1D') ));
            $exercice->setDateFin( $dateDernier->add(new \DateInterval('P1Y') ));
        }

        $form = $this->createForm(ExerciceType::class, $exercice);         
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateDebut = $exercice->getDateDebut();
            $dateFin = $exercice->getDateFin();            
            // verifier date de debut si déja configurer
            if ($dateDebut > $dateDernier) {
                $interval = (int) date_diff($dateDebut, $dateFin)->format('%a');
                // verifie la longueur de l'exercice
                if ($interval == 364 || $interval == 365) {
                    $new  = $exerciceService->createNewExercice($exercice); // Sauvegarde de l'exercice
                    // Si l'exercice est le premier 
                    if (!$session->get('exercice')) {
                        $session->set('exercice', $new);
                    }
                    return $this->redirectToRoute('parametre_exercice');
                } else {
                    $form->get('dateFin')->addError(new FormError("Un exercice doit durée en une année, $interval donné"));
                }
            } else {
                $form->get('dateDebut')->addError(new FormError('Ce date appartient à d\'autre exercice'));
            }
        }

        return $this->render('parametre/configureExercice.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/parametre/exercice/select/{id}", name="parametre_exercice_select")
     */
    public function selectExercice(Exercice $exercice, SessionInterface $session)
    {      
        $session->set('exercice', $exercice);
        return $this->redirectToRoute('parametre_exercice');
    }

    /**
     * @Route("/parametre/exercice/cloturer/{id}", name="parametre_exercice_cloture")
     */
    public function cloturerExercice(Exercice $exercice, ExerciceService $exerciceService)
    {      
        $exerciceService->cloturerExercice($exercice);
        return $this->redirectToRoute('parametre_exercice');
    }

    /**
     * @Route("/parametre/user/all", name="user_all")
     */
    public function allUser(UserRepository $repository, Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $manager)
    {
        $users = $repository->findAll();
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $photoFile = $form->get('photo')->getData();           
            if ($photoFile) {
                $fileName = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move($this->getParameter('users_img_root_directory'), $fileName);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $user->setPhoto($this->getParameter('users_img_directory').'/'.$fileName);
            } else {
                $user->setPhoto('assets/images/users/profile.png');
            }

            switch ($form->get('fonction')->getData()) {
                case 'administrateur':
                    $user->setRoles(['ROLE_ADMIN']);
                    break;
                case 'comptable':
                    $user->setRoles(['ROLE_COMPTABLE']);
                    break;
                case 'gestionnaire':
                    $user->setRoles(['ROLE_GESTIONNAIRE']);
                    break;
            }  

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('user_all');
        }

        return $this->render('security/users.html.twig', [
            'users' => $users,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/parametre/user/{id}/reset", name="user_reset", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function resetPassword(User $user, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $reset = $request->request->get('reset');
        if ($reset) {
            $user->setPassword(
                $passwordEncoder->encodePassword($user, $reset)
            );
            $user->setLost(false);
            $manager->flush();
        }      
        return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
    }

    /**
     * @Route("/user/profile/{id}", name="user_profile", requirements={"id"="\d+"})
     */
    public function profile(User $user)
    {
        return $this->render('parametre/profile.html.twig', [
            'user' => $user,
            'iscurrent' => $user === $this->getUser()
        ]);
    }

    /**
     * @Route("/user/profile/edit", name="user_profile_edit")
     */
    public function editProfile(Request $request, EntityManagerInterface $manager)
    {
        $user = $this->getUser();
        $form = $this->createFormBuilder($user)
                        ->add('username')            
                        ->add('nom')
                        ->add('prenom')
                        ->add('email', EmailType::class)
                        ->add('phone')
                        ->add('photo', FileType::class, [
                            'mapped' => false,
                            'required' => false
                        ])
                        ->getForm();       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();           
            if ($photoFile) {
                $fileName = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move($this->getParameter('users_img_root_directory'), $fileName);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $user->setPhoto($this->getParameter('users_img_directory').'/'.$fileName);
            }
            $manager->flush();

            return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
        }

        return $this->render('parametre/editProfile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/profile/password", name="user_profile_password")
     */
    public function editPassword(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $form = $this->createFormBuilder()
                        ->add('old_password', PasswordType::class)            
                        ->add('new_password', PasswordType::class, [
                            'constraints' => [
                                new NotBlank(['message' => 'Veillez entrez le mot de passe']),
                                new Length(['min' => '6', 'minMessage' => "Votre mot de passe doit faire minimum 6 caractères"])
                            ],
                        ])            
                        ->add('confirm_password', PasswordType::class)
                        ->getForm();       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();         
            if ($passwordEncoder->isPasswordValid($user, $form->get('old_password')->getData())) {
                $new = $form->get('new_password')->getData();
                $confirm = $form->get('confirm_password')->getData();
                if ($new === $confirm) {
                    $user->setPassword(
                        $passwordEncoder->encodePassword($user, $new)
                    );
                    $manager->flush();
                    return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
                } else {
                    $form->get('confirm_password')->addError(new FormError('Votre confirmation de mot de passe est incorrect'));
                }       
            } else {
                $form->get('old_password')->addError(new FormError('Mot de passe incorrect'));
            }
        }

        return $this->render('parametre/editPassword.html.twig', [
            'form' => $form->createView()
        ]);
    }
}