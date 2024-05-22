<?php

namespace App\Controller\Admin\Post;

use App\Entity\Post;
use App\Entity\User;
use DateTimeImmutable;
use App\Form\AdminPostFormType;
use App\Repository\CategoryRepository;
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
        private PostRepository $postRepository,
        private CategoryRepository $categoryRepository
    ) {
    }

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

        // On vérifie qu'il existe au moins une catégorie
        if ( \count($this->categoryRepository->findAll()) == 0 ) 
        {
            $this->addFlash("warning", "Avant de pouvoir créer un article vous devez d'abord créer au moins une catégorie");

            return $this->redirectToRoute("admin_category_index");
        }

        $post = new Post();

        $form = $this->createForm(AdminPostFormType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * Récupérons l'utilisateur connecté
             * 
             * @var User
             */

            $user = $this->getUser();
            $post->setUser($user);

            $post->setUpdatedAt(new DateTimeImmutable());
            $post->setCreatedAt(new DateTimeImmutable());

            $this->entityManager->persist($post);
            $this->entityManager->flush($post);

            $this->addFlash("success", "L'article a été créé et sauvegardé.");

            return $this->redirectToRoute("admin_post_index");
        }

        return $this->render('pages/admin/post/create.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/post/{id<\d+>}/publish', name: 'admin_post_publish', methods: ["POST"])]
    public function publish(Post $post, Request $request): Response
    {
        // Si le csrf token est valide
        if ($this->isCsrfTokenValid('publish_post_' . $post->getId(), $request->request->get('_csrf_token'))) {
            // Si l'article n'a pas encore été publié
            if (false === $post->isPublished()) {
                // On publie l'article
                $post->setPublished(true);

                // Mise à jour de la date de modification
                $post->setUpdatedAt(new DateTimeImmutable());

                // Mise à jour de la date de publication
                $post->setPublishedAt(new DateTimeImmutable());

                // Utilisation de l'entity manager pour préparer la requete
                $this->entityManager->persist($post);

                // Message flash
                $this->addFlash("success", "L'article a été publié.");
            } else {

                // On retire la publication de l'article
                $post->setPublished(false);

                // Mise à jour de la date de modification
                $post->setUpdatedAt(new DateTimeImmutable());

                // Mise à jour de la date de publication
                $post->setPublishedAt(null);

                // Utilisation de l'entity manager pour préparer la requete
                $this->entityManager->persist($post);

                // Message flash
                $this->addFlash("success", "L'article a été retiré de la liste des publications.");
            }

            // On utilise l'entity manager pour exécuter la requête préparer
            $this->entityManager->flush($post);
        }

        // Redirection et on arrete l'execution du script avec return
        return $this->redirectToRoute("admin_post_index");
    }

    #[Route('/post/{id<\d+>}/show', name: 'admin_post_show', methods: ["GET"])]
    public function show(Post $post): Response
    {
        // On vérifie qu'il existe au moins une catégorie
        if ( \count($this->categoryRepository->findAll()) == 0 ) 
        {
            $this->addFlash("warning", "Avant de pouvoir voir un article vous devez d'abord créer au moins une catégorie");

            return $this->redirectToRoute("admin_category_index");
        }

        return $this->render('pages/admin/post/show.html.twig', [
            "post" => $post
        ]);
    }

    #[Route('/post/{id<\d+>}/edit', name: 'admin_post_edit', methods: ["GET", "POST"])]
    public function edit(Post $post, Request $request): Response
    {
        // On vérifie qu'il existe au moins une catégorie
        if ( \count($this->categoryRepository->findAll()) == 0 ) 
        {
            $this->addFlash("warning", "Avant de pouvoir modifier un article vous devez d'abord créer au moins une catégorie");

            return $this->redirectToRoute("admin_category_index");
        }

        $form = $this->createForm(AdminPostFormType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * Récupérons l'utilisateur connecté
             * 
             * @var User
             */

            $user = $this->getUser();
            $post->setUser($user);

            $post->setUpdatedAt(new DateTimeImmutable());

            $this->entityManager->persist($post);
            $this->entityManager->flush($post);

            $this->addFlash("success", "L'article a été modifié et sauvegardé.");

            return $this->redirectToRoute("admin_post_index");
        }

        return $this->render("pages/admin/post/edit.html.twig", [
            "post" => $post,
            "form" => $form->createView()
        ]);
    }

    #[Route('/post/{id<\d+>}/delete', name: 'admin_post_delete', methods: ["POST"])]
    public function delete(Post $post, Request $request): Response
    {
        // Si le csrf token est valide
        if ($this->isCsrfTokenValid('delete_post_' . $post->getId(), $request->request->get('_csrf_token'))) {

            $this->addFlash("danger", "L'article {$post->getTitle()} a été supprimée");

            // On fait appel a entityManager pour préparer la requete
            $this->entityManager->remove($post);

            // On fait appel a entityManager pour exécuter la requete
            $this->entityManager->flush();
        }

        return $this->redirectToRoute("admin_post_index");
    }
}
