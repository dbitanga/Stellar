<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_root')]
    public function root(): RedirectResponse
    {
        // Staff cannot open the admin dashboard, so they land on the orders page.
        return $this->redirectToRoute($this->isGranted('ROLE_ADMIN') ? 'app_dashboard_home' : 'app_dashboard_order_index');
    }

    #[Route('/dashboard', name: 'app_dashboard_home')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(ProductRepository $products, UserRepository $users, OrderRepository $orders): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'page_title' => 'Dashboard',
            'stats' => [
                'Total users' => $users->count([]),
                'Total staff' => $users->countByRole(User::ROLE_STAFF),
                'Total customers' => $users->countByRole(User::ROLE_USER),
                'Total records' => $products->count([]),
                'Revenue' => '₱'.number_format((float) $orders->sumByPaymentStatus(Order::PAYMENT_PAID), 2),
                'Orders to process' => $orders->count(['orderStatus' => Order::STATUS_PROCESSING]),
                'Returns to review' => $orders->count(['orderStatus' => Order::STATUS_RETURN_REQUESTED]),
                'Hidden' => $products->count(['visible' => false]),
            ],
            'recent_orders' => $orders->findRecent(5),
            'low_stock' => $products->findLowStock(),
        ]);
    }
}
