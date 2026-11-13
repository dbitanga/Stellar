<?php

namespace App\Controller;

use App\Dto\Checkout;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Form\CheckoutType;
use App\Service\CartService;
use App\Support\FormErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
#[IsGranted('ROLE_USER')]
class CartController extends AbstractController
{
    use FormErrorsTrait;

    public function __construct(private readonly CartService $cart)
    {
    }

    #[Route('', name: 'app_cart', methods: ['GET'])]
    public function index(#[CurrentUser] User $user): Response
    {
        return $this->render('store/cart.html.twig', [
            'lines' => $this->cart->lines($user),
            'quote' => $this->cart->quote($user),
            'checkout_form' => $this->createForm(CheckoutType::class, Checkout::forCustomer($user), [
                'action' => $this->generateUrl('app_cart_checkout'),
            ]),
        ]);
    }

    /** Called with fetch() from the products page (answers JSON for the "added" pop-up), or as a plain form post. */
    #[Route('/add/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid('cart', tokenKey: '_token')]
    public function add(Request $request, #[CurrentUser] User $user, Product $product): Response
    {
        try {
            $this->cart->add($user, $product);
            $result = ['success' => true, 'item' => ['name' => $product->getName(), 'image' => $request->getBasePath().'/'.$product->getImagePath()]];
        } catch (\DomainException $e) {
            $result = ['success' => false, 'message' => $e->getMessage()];
        }

        if ('json' === $request->getPreferredFormat()) {
            return $this->json($result, $result['success'] ? 200 : 409);
        }
        $this->addFlash($result['success'] ? 'success' : 'danger', $result['success'] ? sprintf('%s added to your cart.', $product->getName()) : $result['message']);

        return $this->redirectToRoute('app_products');
    }

    #[Route('/{id}/{action}', name: 'app_cart_change', requirements: ['id' => '\d+', 'action' => 'increase|decrease|remove'], methods: ['POST'])]
    #[IsCsrfTokenValid('cart', tokenKey: '_token')]
    public function change(#[CurrentUser] User $user, Product $product, string $action): Response
    {
        try {
            match ($action) {
                'increase' => $this->cart->change($user, $product, 1),
                'decrease' => $this->cart->change($user, $product, -1),
                'remove' => $this->cart->remove($user, $product),
            };
        } catch (\DomainException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    public function checkout(Request $request, #[CurrentUser] User $user): Response
    {
        $checkout = new Checkout();
        $form = $this->createForm(CheckoutType::class, $checkout);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->flashFormErrors($form);

            return $this->redirectToRoute('app_cart');
        }

        try {
            $order = $this->cart->checkout($user, $checkout);
        } catch (\DomainException $e) {
            $this->addFlash('danger', $e->getMessage());

            return $this->redirectToRoute('app_cart');
        }

        return $this->redirectToRoute('app_cart_confirmed', ['id' => $order->getId()]);
    }

    #[Route('/confirmed/{id}', name: 'app_cart_confirmed', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function confirmed(#[CurrentUser] User $user, Order $order): Response
    {
        if ($order->getUser() !== $user) {
            throw $this->createNotFoundException();
        }

        return $this->render('store/order_confirmed.html.twig', [
            'order' => $order,
            'delivery_from' => $order->getCreatedAt()->modify('+10 days'),
            'delivery_to' => $order->getCreatedAt()->modify('+15 days'),
        ]);
    }
}
