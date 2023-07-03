<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentCreateFormType;
use App\Form\PostCreateFormType;
use App\Form\PostEditType;
use PHPUnit\Framework\Constraint\DirectoryExists;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class PostController extends AbstractController
{
    #[Route('/posts', name: 'posts')]
    public function index(ManagerRegistry $doctrine): Response
    {

        $posts = $doctrine->getRepository(Post::class)->findAll();
        if ($posts != null) {
            return $this->render('post/index.html.twig', ['posts' => $posts,
            ]);
        } else
            return $this->render('post/index.html.twig', [ 'posts' => null
            ]);
    }

    #[Route('/posts/{post_id}', name: 'show_one_post')]
    public function show_one_post(ManagerRegistry $doctrine, Request $request, $post_id): Response
    {

        $timezone = new \DateTimeZone('Europe/Moscow');
        $currentDateTime = new \DateTime();
        $currentDateTime->setTimezone($timezone);

        $comment = new Comments();
        $form = $this->createForm(CommentCreateFormType::class);
        $form->handleRequest($request);
        /**
         * @var Post $post
         */
        $post = $doctrine->getRepository(Post::class)->findBy(['id' => $post_id]);
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setOwner($user);
            $comment->setContent($form->get('content')->getData());
            $comment->setDeletedAt(false);
            $comment->setCreationDate($currentDateTime);
            $comment->setPost($post[0]);
            $em = $doctrine->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('show_one_post',
                ['post_id' => $post_id]);
        }

        $comments = $doctrine->getRepository(Comments::class)->findBy(['post'=>$post_id]);
        $post = $doctrine->getRepository(Post::class)->findBy(['id' => $post_id]);
        return $this->render('post/show_one_post.html.twig', [
            'post' => $post[0],
            'comments' => $comments,
            'comment_form' => $form->createView(),
            'date_time' => $currentDateTime
        ]);
    }

    #[Route('/comments/edit/{comment_id}', name: 'edit_comment')]
    public function edit(ManagerRegistry $doctrine, Request $request, $comment_id): Response
    {

        /**
         * @var Comments $comment
         */
        $em = $doctrine->getManager();
        $comment = $doctrine->getRepository(Comments::class)->find($comment_id);

        if (!$comment) {
            throw $this->createNotFoundException('Комментарий не найден');

        }
        $post_id = $comment->getPost();

        $form = $this->createForm(CommentCreateFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('show_one_post', ['post_id' => $comment->getPost()->getId()]);
        }

        return $this->render('post/show_one_post.html.twig', [
            'comments' => $comment,
            'comment_form' => $form->createView(),
            'post' => $post_id
        ]);
    }

    #[Route('/posts/user/{user_id}', name: 'show_user_posts')]
    public function show_user_posts(ManagerRegistry $doctrine, $user_id): Response
    {


        $posts = $doctrine->getRepository(Post::class)->findBy(['owner' => $user_id]);
        $user = $doctrine->getRepository(User::class)->findBy(['id' => $user_id]);
        return $this->render('post/show_user_post.html.twig', [ 'posts' => $posts, 'user' => $user[0],
        ]);
    }

    #[Route('/my_posts', name: 'my_posts')]
    public function show_my_posts(ManagerRegistry $doctrine): Response
    {
        /**
         * @var User $user
         */

        $user = $this->getUser();
        $posts = $doctrine->getRepository(Post::class)->findBy(['owner' => $user]);

        return $this->render('post/show_my_posts.html.twig', [ 'posts' => $posts,
        ]);
    }



    #[Route('/add_post', name: 'add_post')]
    public function add_post(Request $request, ManagerRegistry $doctrine): Response
    {

        $timezone = new \DateTimeZone('Europe/Moscow');
        $currentDateTime = new \DateTime();
        $currentDateTime->setTimezone($timezone);


        $post = new Post();
        $form = $this->createForm(PostCreateFormType::class);
        $form->handleRequest($request);

        /**
         * @var User $user
         */

        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setHeading($form->get('Title')->getData());
            $post->setContent($form->get('Content')->getData());
            $post->setOwner($user);
            $post->setCreationDate($currentDateTime);
            $post->setDeletedAt(false);
            $user->setIsActive(true);

             $em = $doctrine->getManager();
             $em->persist($post);
             $em->flush();

            return $this->redirectToRoute('posts');
        }


        return $this->render('post/add_post.html.twig', [ 'form' => $form->createView()
        ]);
    }

    #[Route('/edit_post/{post_id}', name: 'edit_post')]
    public function edit_post(Request $request, ManagerRegistry $doctrine, $post_id): Response
    {


        /**
         * @var User $user
         */

        $user = $this->getUser();
        if ($post_id !== null) {
            $post = $doctrine->getRepository(Post::class)->find($post_id);
            if (!$post || $post->getOwner() !== $user) {
                throw $this->createNotFoundException('Пост не найден');
            }
            $form = $this->createForm(PostEditType::class);
            $form->handleRequest($request);
            if (!$form->isSubmitted()) {
                $form->setData($post);
            }
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setHeading($form->get('heading')->getData());
            $post->setContent($form->get('content')->getData());

            $em = $doctrine->getManager();
            $em->flush();

            return $this->redirectToRoute('my_posts', ['post_id' => $post_id]);
        }


        return $this->render('post/edit_post.html.twig', [ 'EditForm' => $form->createView()
        ]);
    }

    #[Route('/delete_post/{post_id}', name: 'delete_post')]
    public function delete_post(Request $request, ManagerRegistry $doctrine, $post_id): Response
    {


        /**
         * @var User $user
         */

        $user = $this->getUser();
            $post = $doctrine->getRepository(Post::class)->find($post_id);
            $post->setDeletedAt(true);

            $em = $doctrine->getManager();
            $em->persist($post);
            $em->flush();

        return $this->redirectToRoute('my_posts');

    }
}
