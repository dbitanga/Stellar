<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DashboardHeaderController extends AbstractController
{
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('dashboard_header/index.html.twig', [
            'site_name' => 'STELLAR',
            'user_name' => $user instanceof User ? ($user->getFullName() ?: $user->getUsername()) : '',
            'role_label' => $user instanceof User ? $user->getRoleLabel() : '',
        ]);
    }
}
