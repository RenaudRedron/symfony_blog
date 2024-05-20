<?php

namespace App\Controller\Admin\Category;

use App\Entity\Category;
use App\Form\AdminCategoryFormType;
use App\Repository\CategoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class CategoryController extends AbstractController
{   

    public function __construct( 
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository
    )
    {}

    #[Route('/category/list', name: 'admin_category_index', methods: ["GET"])]
    public function index(): Response
    {
        return $this->render('pages/admin/category/index.html.twig', [
            "categories" => $this->categoryRepository->findAll()
        ]);
    }

    #[Route('/category/create', name: 'admin_category_create', methods: ["GET","POST"])]
    public function create(Request $request): Response
    {   
        $category = new Category();
        $form = $this->createForm(AdminCategoryFormType::class, $category);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid())
        {

            $category->setCreatedAt(new DateTimeImmutable());
            $category->setUpdatedAt(new DateTimeImmutable());

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash("success", "La catégorie a été ajoutée avec succès");

            return $this->redirectToRoute("admin_category_index");

        }

        return $this->render('pages/admin/category/create.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/category/{id<\d+>}/edit', name: 'admin_category_edit', methods: ["GET","POST"])]
    public function edit(Category $category, Request $request): Response
    {  
        // S'il on le souhaite on peu utilisé notre objet entity qui va généré le code ci-dessous automatiquement et donc pas besoins de tout tapé nous même mais c'est fesable si on le souhaite
        // $category = $this->categoryRepository->find((int) $id);

        // if ( null === $category )
        // {
        //     return $this->redirectToRoute('admin_category_index')
        // }

        $form = $this->createForm(AdminCategoryFormType::class, $category);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid())
        {

            $category->setUpdatedAt(new DateTimeImmutable());

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash("primary", "La catégorie '{$category->getName()}' a été modifier avec succès");

            return $this->redirectToRoute("admin_category_index");

        }

        return $this->render('pages/admin/category/edit.html.twig', [
            "form" => $form->createView(),
            "category" => $category
        ]);
    }

    #[Route('/category/{id<\d+>}/delete', name: 'admin_category_delete', methods: ["POST"])]
    public function delete(Category $category, Request $request): Response
    {  
        if ( $this->isCsrfTokenValid('delete_category_'.$category->getId(), $request->request->get('_csrf_token') ) )
        {
            // Si le token est valide

            $this->addFlash("danger", "La catégorie {$category->getName()} a été supprimée");

            // On fait appel a entityManager pour préparer la requete
            $this->entityManager->remove($category);

            // On fait appel a entityManager pour exécuter la requete
            $this->entityManager->flush();

        }

        return $this->redirectToRoute("admin_category_index");
    }

}