<?php

namespace App\Controller\Admin\Post;

use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        return $this->render('pages/admin/post/index.html.twig');
    }

    #[Route('/post/create', name: 'admin_post_create', methods: ["GET", "POST"])]
    public function create(): Response
    {

        


        return $this->render('pages/admin/post/create.html.twig');
    }

}
