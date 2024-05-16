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

}