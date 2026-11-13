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

        // 'role' is checked with is_granted() in the template, so staff never see admin-only links.
        $menu = [
            ['label' => 'Dashboard', 'route' => 'app_dashboard_home', 'prefix' => 'app_dashboard_home', 'role' => 'ROLE_ADMIN'],
            ['label' => 'Orders', 'route' => 'app_dashboard_order_index', 'prefix' => 'app_dashboard_order', 'role' => 'ROLE_STAFF'],
            ['label' => 'Payments', 'route' => 'app_dashboard_payment_index', 'prefix' => 'app_dashboard_payment', 'role' => 'ROLE_ADMIN'],
            ['label' => 'Products', 'route' => 'app_dashboard_product_index', 'prefix' => 'app_dashboard_product', 'role' => 'ROLE_STAFF'],
            ['label' => 'Customers', 'route' => 'app_dashboard_customer_index', 'prefix' => 'app_dashboard_customer', 'role' => 'ROLE_ADMIN'],
            ['label' => 'Staff', 'route' => 'app_dashboard_user_index', 'prefix' => 'app_dashboard_user', 'role' => 'ROLE_ADMIN'],
            ['label' => 'My Profile', 'route' => 'app_profile', 'prefix' => 'app_profile', 'role' => 'ROLE_STAFF'],
        ];

        return $this->render('dashboard_sidebar/index.html.twig', [
            'menu' => $menu,
            'current_route' => $currentRoute,
        ]);
    }
}