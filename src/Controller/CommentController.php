<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentCreateFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/comments/delete/{comment_id}', name: 'delete_comment')]
    public function delete(ManagerRegistry $doctrine, $comment_id): Response
    {
        $em = $doctrine->getManager();
        $comment = $doctrine->getRepository(Comments::class)->find($comment_id);

        if (!$comment) {
            throw $this->createNotFoundException('Комментарий не найден');
        }


        $comment->setDeletedAt(true);
        $em->flush();

        return $this->redirectToRoute('show_one_post', ['post_id' => $comment->getPost()->getId()]);
    }

}
