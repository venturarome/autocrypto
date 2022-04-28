<?php

namespace App\Presentation\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/', name: 'admin', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('@Web/Admin/index.html.twig');
    }
}
