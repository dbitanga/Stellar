<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** Payments are part of each order, so this page is a money-focused view of the orders table. */
#[Route('/dashboard/payment')]
#[IsGranted('ROLE_ADMIN')]
class DashboardPaymentController extends AbstractController
{
    #[Route('', name: 'app_dashboard_payment_index', methods: ['GET'])]
    public function index(OrderRepository $orders): Response
    {
        return $this->render('dashboard_payment/index.html.twig', [
            'page_title' => 'Payments',
            'orders' => $orders->findAllWithDetails(),
            'totals' => [
                'Collected' => $orders->sumByPaymentStatus(Order::PAYMENT_PAID),
                'Awaiting payment' => $orders->sumByPaymentStatus(Order::PAYMENT_PENDING),
                'Refunded' => $orders->sumByPaymentStatus(Order::PAYMENT_REFUNDED),
            ],
        ]);
    }
}
