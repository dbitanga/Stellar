<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** Customers sign up on the store, so the admin only views, disables or deletes them (via DashboardUserController). */
#[Route('/dashboard/customer')]
#[IsGranted('ROLE_ADMIN')]
class DashboardCustomerController extends AbstractController
{
    #[Route('', name: 'app_dashboard_customer_index', methods: ['GET'])]
    public function index(UserRepository $users): Response
    {
        return $this->render('dashboard_customer/index.html.twig', [
            'page_title' => 'Customers',
            'customers' => $users->findCustomersWithStats(),
        ]);
    }
}
