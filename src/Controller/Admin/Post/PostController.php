<?php

namespace App\Controller\Admin\Post;

use App\Entity\Post;
use App\Entity\User;
use DateTimeImmutable;
use App\Form\AdminPostFormType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class PostController extends AbstractController
{

    public function __construct( 
        private EntityManagerInterface $entityManager,
        private PostRepository $postRepository
    )
    {}

    #[Route('/post/list', name: 'admin_post_index', methods: ["GET"])]
    public function index(): Response
    {
        return $this->render('pages/admin/post/index.html.twig', [
            "posts" => $this->postRepository->findAll()
        ]);
    }

    #[Route('/post/create', name: 'admin_post_create', methods: ["GET", "POST"])]
    public function create(Request $request): Response
    {

        $post = new Post();

        $form = $this->createForm(AdminPostFormType::class, $post);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid())
        {

            /**
             * Récupérons l'utilisateur connecté
             * 
             * @var User
             */

            $user = $this->getUser();
            $post->setUser($user);

            $post->setUpdatedAt( new DateTimeImmutable());
            $post->setCreatedAt( new DateTimeImmutable());

            $this->entityManager->persist($post);
            $this->entityManager->flush($post);

            $this->addFlash("success","L'article a été créé et sauvegardé.");

            return $this->redirectToRoute("admin_post_index");
        }

        return $this->render('pages/admin/post/create.html.twig', [
            "form" => $form->createView()
        ]);
    }

}
