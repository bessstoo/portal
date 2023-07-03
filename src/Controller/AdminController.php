<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\BanFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_page')]
    public function admin_panel(ManagerRegistry $doctrine): Response
    {
        $users = $doctrine->getRepository(User::class)->findAll();
        return $this->render('admin/index.html.twig', [ 'users' =>$users
        ]);
    }

    #[Route('/admin/ban/{user_id}', name: 'admin_ban')]
    public function admin_ban(ManagerRegistry $doctrine, Request $request, $user_id): Response
    {
        $timezone = new \DateTimeZone('Europe/Moscow');
        $currentDateTime = new \DateTime();
        $currentDateTime->setTimezone($timezone);

        /**
         * @var User $user
         */
        $user = $doctrine->getRepository(User::class)->findBy(['id' => $user_id]);
        $form = $this->createForm(BanFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user[0]->setBannedUntil($form->get('bannedUntil')->getData());
            $user[0]->setRoles(['ROLE_BANNED']);

            $em = $doctrine->getManager();
            $em->persist($user[0]);
            $em->flush();
            return $this->redirectToRoute('admin_page');
        }


        return $this->render('admin/admin_ban.html.twig', [
            'ban_form' => $form->createView(),
            'user' => $user[0]
        ]);
    }

    #[Route('/admin/unban/{user_id}', name: 'admin_unban')]
    public function admin_unban(ManagerRegistry $doctrine, Request $request, $user_id): Response
    {

        /**
         * @var User $user
         */
        $user = $doctrine->getRepository(User::class)->findBy(['id' => $user_id]);
        $user[0]->setBannedUntil(null);
        $user[0]->setRoles(['ROLE_USER']);
        $em = $doctrine->getManager();
        $em->persist($user[0]);
        $em->flush();

        return $this->redirectToRoute('admin_page');

    }
}
