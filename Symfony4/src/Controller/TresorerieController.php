<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Article;
use App\Entity\Journal;
use App\Form\CompteType;
use App\Service\ComptaService;
use Doctrine\ORM\EntityRepository;
use App\Repository\CompteRepository;
use App\Repository\ArticleRepository;
use App\Repository\JournalRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TresorerieController extends AbstractController
{
    /**
     * @Route("/comptabilite/tresorerie", name="tresorerie")
     */
    public function index(CompteRepository $repositoryCompte)
    {
        return $this->render('tresorerie/index.html.twig', [
            'comptes' => $repositoryCompte->findTresorerie()
        ]);
    }

    /**
     * @Route("/comptabilite/tresorerie/ajout", name="tresorerie_add")
     * @Route("/comptabilite/tresorerie/{id}/edit", name="tresorerie_edit", requirements={"id"="\d+"})
     */
    public function add(Compte $compteTresorerie = null, Request $request, EntityManagerInterface $manager)
    {
        $data = [];
        if ($compteTresorerie != null) {
            $data['compte'] = $compteTresorerie;
            $data['acceptIn'] = $compteTresorerie->getAcceptIn();
            $data['acceptOut'] = $compteTresorerie->getAcceptOut();
            $data['codeJournal'] = $compteTresorerie->getCodeJournal();
            $data['note'] = $compteTresorerie->getNote();
        }
        $form = $this->createFormBuilder($data)
                      ->add('compte', EntityType::class, [
                        'class' => Compte::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->andWhere('c.classe = \'5-COMPTES FINANCIERS\'')
                                    ->andWhere('length(c.poste)=6')
                                    ->orderBy('c.poste', 'ASC');
                        },
                        'choice_label' => function ($c) {
                          return $c->getTitre().' ('.$c->getPoste().')';
                      }
                      ])
                      ->add('codeJournal')
                      ->add('acceptIn', ChoiceType::class, [
                        'choices'  => ['Oui'=> true,'Non'=> false]
                      ])
                      ->add('acceptOut', ChoiceType::class, [
                        'choices'  => ['Oui'=> true,'Non'=> false]
                      ])     
                      ->add('note', TextareaType::class)
                      ->getForm();       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          $alreadyTresor = false;
          if ($compteTresorerie == null) { // On est en mode création
            $compteTresorerie = $form->get('compte')->getData();
            
            if ($compteTresorerie->getIsTresor())
              $alreadyTresor = true;
            else {
              $createdJournal = new Journal("Trésorerie",$form->get('codeJournal')->getData(),$compteTresorerie->getTitre());
              $manager->persist($createdJournal);
              $compteTresorerie->setIsTresor(true);
            }
          }
          if (!$alreadyTresor) {
            $compteTresorerie->setCodeJournal($form->get('codeJournal')->getData());
            $compteTresorerie->setAcceptIn($form->get('acceptIn')->getData());
            $compteTresorerie->setAcceptOut($form->get('acceptOut')->getData());
            $compteTresorerie->setNote($form->get('note')->getData());

            $manager->persist($compteTresorerie);
            $manager->flush();
            return $this->redirectToRoute('tresorerie');
          } else {
            $form->get('compte')->addError(new FormError("Ce compte financier est déja rattaché à un autre tresorerie"));
            $compteTresorerie = null;
          }
        }

        return $this->render('tresorerie/form.html.twig', [
            'form' => $form->createView(),
            'editMode' => $compteTresorerie !== null
        ]);
    }

    /**
     * @Route("/comptabilite/tresorerie/cheque/{id}", name="tresorerie_cheque")
     */
    public function manageCheque(Compte $compteCheque, ArticleRepository $repository, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        return $this->render('tresorerie/cheque.html.twig', [
            'compte' => $compteCheque,
            'cheques' => $repository->findCheques($exercice, $compteCheque),
        ]);
    }

    /**
     * @Route("/comptabilite/tresorerie/cheque/{id}/verser", name="tresorerie_cheque_verser")
     */
    public function versementCheque(Article $articleCheque, Request $request, ComptaService $comptaService)
    {
        $form = $this->createFormBuilder()
                    ->add('date', DateType::class, [
                        'data' => new \DateTime(),
                        'constraints' => [new LessThanOrEqual("today")]
                    ])
                    ->add('banque', EntityType::class, [
                        'class' => Compte::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->andWhere('c.isTresor = true')
                                    ->andWhere('c.poste LIKE :poste')
                                    ->setParameter('poste', '512%')
                                    ->orderBy('c.poste', 'ASC');
                        },
                        'choice_label' => 'titre'
                    ])
                    ->add('piece', TextType::class)
                    ->getForm();       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = $form->get('date')->getData();
            $banque = $form->get('banque')->getData();
            $piece = $form->get('piece')->getData();
            if ($comptaService->verserCheque($articleCheque, $banque, $date, $piece)) // Si success
                return $this->redirectToRoute('tresorerie_cheque', ['id'=>$articleCheque->getCompteDebit()->getId()]);
        }

        return $this->render('tresorerie/versementCheque.html.twig', [
            'cheque' => $articleCheque,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/comptabilite/codes_journaux", name="tresorerie_codes_journaux")
     */
    public function codesJournaux(Request $request,JournalRepository $repo, EntityManagerInterface $manager )
    {
      if ($request->getMethod() == "POST") {
        $type = $request->request->get('j_type','Achats');
        $code = $request->request->get('j_code');
        $intitule = $request->request->get('j_intitule');
        $j = new Journal($type,$code,$intitule);
        $manager->persist($j);
        $manager->flush();
      }

      return $this->render('tresorerie/journaux.html.twig',[
        'journaux'=>$repo->findCodes()
      ]);
    }
}
