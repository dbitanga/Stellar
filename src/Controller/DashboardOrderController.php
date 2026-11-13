<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\SimulateOrderType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\OrderService;
use App\Support\FormErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Staff process orders (ship, deliver, cancel, reject a return).
 * Only admins can simulate orders, mark them paid, or approve a return and refund it.
 */
#[Route('/dashboard/order')]
#[IsGranted('ROLE_STAFF')]
class DashboardOrderController extends AbstractController
{
    use FormErrorsTrait;

    public function __construct(private readonly OrderService $orders)
    {
    }

    #[Route('', name: 'app_dashboard_order_index', methods: ['GET'])]
    public function index(OrderRepository $orders, ProductRepository $products): Response
    {
        $available = $products->findAvailable();

        return $this->render('dashboard_order/index.html.twig', [
            'page_title' => 'Orders',
            'orders' => $orders->findAllWithDetails(),
            'products' => $available,
            'form' => $this->isGranted('ROLE_ADMIN')
                ? $this->createForm(SimulateOrderType::class, null, [
                    'products' => $available,
                    'action' => $this->generateUrl('app_dashboard_order_simulate'),
                ])
                : null,
        ]);
    }

    #[Route('/simulate', name: 'app_dashboard_order_simulate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function simulate(Request $request, ProductRepository $products): Response
    {
        $form = $this->createForm(SimulateOrderType::class, null, ['products' => $products->findAvailable()]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->flashFormErrors($form);

            return $this->redirectToRoute('app_dashboard_order_index');
        }

        $data = $form->getData();
        $quantities = [];
        foreach ($data as $field => $value) {
            if (str_starts_with($field, 'qty_')) {
                $quantities[(int) substr($field, 4)] = (int) $value;
            }
        }

        try {
            $order = $this->orders->place($data['customer'], $quantities, $data['paymentMethod'], $data['returnReason']);
        } catch (\DomainException $e) {
            $this->addFlash('danger', $e->getMessage());

            return $this->redirectToRoute('app_dashboard_order_index');
        }

        $this->addFlash('success', sprintf('Order #%d placed for %s.', $order->getId(), $order->getShippingName()));

        return $this->redirectToRoute('app_dashboard_order_show', ['id' => $order->getId()]);
    }

    #[Route('/{id}', name: 'app_dashboard_order_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('dashboard_order/show.html.twig', [
            'page_title' => sprintf('Order #%d', $order->getId()),
            'order' => $order,
        ]);
    }

    #[Route('/{id}/ship', name: 'app_dashboard_order_ship', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function ship(Request $request, Order $order): Response
    {
        return $this->apply($request, $order, 'ship', fn () => $this->orders->ship($order), 'marked as shipped');
    }

    #[Route('/{id}/deliver', name: 'app_dashboard_order_deliver', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deliver(Request $request, Order $order): Response
    {
        return $this->apply($request, $order, 'deliver', fn () => $this->orders->deliver($order), 'marked as delivered');
    }

    #[Route('/{id}/cancel', name: 'app_dashboard_order_cancel', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function cancel(Request $request, Order $order): Response
    {
        return $this->apply($request, $order, 'cancel', fn () => $this->orders->cancel($order), 'cancelled');
    }

    #[Route('/{id}/pay', name: 'app_dashboard_order_pay', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function pay(Request $request, Order $order): Response
    {
        return $this->apply($request, $order, 'pay', fn () => $this->orders->markPaid($order), 'marked as paid');
    }

    #[Route('/{id}/refund', name: 'app_dashboard_order_refund', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function refund(Request $request, Order $order): Response
    {
        return $this->apply($request, $order, 'refund', fn () => $this->orders->refund($order), 'returned and refunded');
    }

    #[Route('/{id}/reject-return', name: 'app_dashboard_order_reject_return', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function rejectReturn(Request $request, Order $order): Response
    {
        return $this->apply($request, $order, 'reject_return', fn () => $this->orders->rejectReturn($order), 'return rejected; the order stays delivered');
    }

    /** Checks the CSRF token, runs the action, flashes the result and goes back to where the button was. */
    private function apply(Request $request, Order $order, string $action, callable $run, string $done): Response
    {
        if (!$this->isCsrfTokenValid($action.'_order'.$order->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Invalid token. Please try again.');
        } else {
            try {
                $run();
                $this->addFlash('success', sprintf('Order #%d %s.', $order->getId(), $done));
            } catch (\DomainException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        $back = $request->getPayload()->getString('_back');
        if (\in_array($back, ['app_dashboard_order_index', 'app_dashboard_payment_index'], true)) {
            return $this->redirectToRoute($back);
        }

        return $this->redirectToRoute('app_dashboard_order_show', ['id' => $order->getId()]);
    }
}
