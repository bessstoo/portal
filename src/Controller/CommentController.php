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

    #[Route('/comments/reply/{comment_id}', name: 'reply_comment')]
    public function reply(ManagerRegistry $doctrine, Request $request, $comment_id): Response
    {

        $timezone = new \DateTimeZone('Europe/Moscow');
        $currentDateTime = new \DateTime();
        $currentDateTime->setTimezone($timezone);

        $em = $doctrine->getManager();
        $comment = $doctrine->getRepository(Comments::class)->find($comment_id);

        if (!$comment) {
            throw $this->createNotFoundException('Комментарий не найден');
        }

        $reply_comment = new Comments();
        $form = $this->createForm(CommentCreateFormType::class);
        $form->handleRequest($request);

        $user = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            $reply_comment->setOwner($user);
            $reply_comment->setContent($form->get('content')->getData());
            $reply_comment->setDeletedAt(false);
            $reply_comment->setCreationDate($currentDateTime);
            $reply_comment->setPost($comment->getPost());
            $reply_comment->setReplyId($comment->getId());
            $em->persist($reply_comment);
            $em->flush();

            return $this->redirectToRoute('show_one_post', ['post_id' => $comment->getPost()->getId()]);
        }

        return $this->render('post/show_one_post.html.twig', [
            'comments' => $comment,
//            'reply_comments' => $reply_comment,
            'comment_form' => $form->createView(),
            'post' => $comment->getPost()
        ]);
    }

}
