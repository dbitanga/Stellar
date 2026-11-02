<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function root(): RedirectResponse
    {
        return $this->redirectToRoute('app_dashboard_home');
    }

    #[Route('/dashboard', name: 'app_dashboard_home')]
    public function index(ProductRepository $products, CategoryRepository $categories): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'page_title' => 'Dashboard',
            'stats' => [
                'Products' => $products->count([]),
                'Hidden' => $products->count(['visible' => false]),
                'Low stock' => $products->countLowStock(),
                'Categories' => $categories->count([]),
            ],
        ]);
    }
}