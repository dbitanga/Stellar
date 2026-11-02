<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DashboardHeaderController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('dashboard_header/index.html.twig', [
            'site_name' => 'Admin',
            'user_name' => 'Administrator',
        ]);
    }
}