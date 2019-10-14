<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\AddpostType;
use App\Form\DeclinePostType;
use App\Form\FullpostType;
use App\Form\PostType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $yee = $this->getDoctrine()->getRepository(Post::class)->findBy(['visible' => true]);

        return $this->render('default/index.html.twig', [
            'posts' => $yee
        ]);
    }

    /**
     * @Route("/fullpost/{id}", name="fullpost")
     */
    public function fullpost($id, Request $request)
    {
        $fullpost = $this->getDoctrine()->getRepository(Post::class)->find($id);

        $comment = new Comment();
        $form = $this->createForm(FullpostType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $comment->setUser($this->getUser());
            $comment->setPost($fullpost);

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirect('/fullpost/'. $id);
        }

        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(['post' => $id]);

        return $this->render('default/fullpost.html.twig', [
            'post' => $fullpost,
            'comment' => $comments,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/addpost/", name="addpost")
     */
    public function addpost(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(AddpostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $post->setUser($this->getUser());
            $post->setVisible(false);
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirect('/');
        }

        return $this->render('default/addpost.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/acceptuser/", name="acceptuser")
     */
    public function acceptuser()
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('default/showusers.html.twig', [
            'users' => $user
        ]);
    }


    /**
     * @Route("/acceptpost/{id?}", name="acceptpost", methods={"GET", "POST"})
     */
    public function acceptpost(Request $request, $id)
    {
        $posts = $this->getDoctrine()->getRepository(Post::class)->findBy(['visible' => false, 'reason' => null]);

        if(isset($_POST['submit'])){
            $text = $_POST['text'];
            $findid = $this->getDoctrine()->getRepository(Post::class)->find($id);
            $em = $this->getDoctrine()->getManager();
            $findid->setReason($text);
            $em->persist($findid);
            $em->flush();

            return $this->redirect('/');
        }

        return $this->render('default/acceptpost.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/accept/{id}", name="accept")
     */
    public function accept(Request $request, $id)
    {
        $findid = $this->getDoctrine()->getRepository(Post::class)->find($id);

        $em = $this->getDoctrine()->getManager();
        $findid->setVisible(true);
        $em->persist($findid);
        $em->flush();

        return $this->redirect('/');
    }

    /**
     * @Route("/unacceptedpost/", name="yeet")
     */
    public function unnpost()
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->findBy(['user' => $this->getUser(), 'visible' => false, 'reason' => null]);
        $accpost = $this->getDoctrine()->getRepository(Post::class)->findBy(['user' => $this->getUser(), 'visible' => true]);
        $decpost = $this->getDoctrine()->getRepository(Post::class)->findBy(['user' => $this->getUser(), 'visible' => false]);

        return $this->render('default/unacceptedpost.html.twig', [
            'posts' => $post,
            'accpost' => $accpost,
            'decpost' => $decpost
        ]);
    }
}
