<?php

namespace App\Controller;


use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $timezone = new \DateTimeZone('Europe/Moscow');
        $currentDateTime = new \DateTime();
        $currentDateTime->setTimezone($timezone);
        $currentDateTime->modify('+3 hours');

        /**
         * @var User $current_user
         */

        $current_user = $this->getUser();
            if ($current_user){
                $roles = $current_user->getRoles()[0];
                if ($roles == 'ROLE_BANNED'){
                    $banned_until = $current_user->getBannedUntil();
                    if ($currentDateTime > $banned_until) {
                        $current_user->setBannedUntil(null);
                        $current_user->setRoles(['ROLE_USER']);

                        $em = $doctrine->getManager();
                        $em->persist($current_user);
                        $em->flush();
                }
            }
        }


        $users = $doctrine->getRepository(User::class)->findby(['is_active' => true]);
        return $this->render('page/index.html.twig', [ 'users' =>$users
        ]);
    }
}
