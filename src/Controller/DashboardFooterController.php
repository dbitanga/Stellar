<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DashboardFooterController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('dashboard_footer/index.html.twig', [
            'year' => date('Y'),
            'site_name' => 'Admin',
        ]);
    }
}