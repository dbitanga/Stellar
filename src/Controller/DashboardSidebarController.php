<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class DashboardSidebarController extends AbstractController
{
    public function index(RequestStack $requestStack): Response
    {
        $currentRoute = $requestStack->getMainRequest()?->attributes->get('_route');

        $menu = [
            ['label' => 'Dashboard', 'route' => 'app_dashboard_home', 'prefix' => 'app_dashboard_home'],
            ['label' => 'Products', 'route' => 'app_dashboard_product_index', 'prefix' => 'app_dashboard_product'],
            ['label' => 'Categories', 'route' => 'app_dashboard_category_index', 'prefix' => 'app_dashboard_category'],
        ];

        return $this->render('dashboard_sidebar/index.html.twig', [
            'menu' => $menu,
            'current_route' => $currentRoute,
        ]);
    }
}