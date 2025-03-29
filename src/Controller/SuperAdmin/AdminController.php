<?php

namespace App\Controller\SuperAdmin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/superadmin/add', name: 'superadmin_add_admin')]
    public function index(): Response
    {
        return $this->render('superadmin/add_admin.html.twig');
    }
}
