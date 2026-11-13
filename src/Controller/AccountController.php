<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\ProfileType;
use App\Repository\OrderRepository;
use App\Service\OrderService;
use App\Support\FormErrorsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** The customer's account page on the store: their orders, profile, password, and closing the account. */
#[Route('/account')]
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    use FormErrorsTrait;

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'app_account', methods: ['GET'])]
    public function index(#[CurrentUser] User $user, OrderRepository $orders): Response
    {
        return $this->render('store/account.html.twig', [
            'orders' => $orders->findForCustomer($user),
            'return_reasons' => Order::RETURN_REASONS,
            'profile_form' => $this->createForm(ProfileType::class, $user, ['action' => $this->generateUrl('app_account_edit')]),
            'password_form' => $this->createForm(ChangePasswordType::class, null, ['action' => $this->generateUrl('app_account_password')]),
        ]);
    }

    #[Route('/edit', name: 'app_account_edit', methods: ['POST'])]
    public function edit(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Your account has been updated.');
        } else {
            $this->flashFormErrors($form);
            // Throw away the rejected values so they can't be saved by a later flush.
            $this->em->refresh($user);
        }

        return $this->redirectToRoute('app_account');
    }

    #[Route('/password', name: 'app_account_password', methods: ['POST'])]
    public function changePassword(Request $request, #[CurrentUser] User $user, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('newPassword')->getData()));
            $this->em->flush();
            $this->addFlash('success', 'Your password has been changed.');
        } else {
            $this->flashFormErrors($form);
        }

        return $this->redirectToRoute('app_account');
    }

    /** Customers can cancel while the order is still processing; stock goes back and card payments are refunded. */
    #[Route('/order/{id}/cancel', name: 'app_account_order_cancel', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function cancelOrder(Request $request, #[CurrentUser] User $user, Order $order, OrderService $orders): Response
    {
        return $this->applyToOwnOrder($request, $user, $order, 'cancel_order', function () use ($orders, $order): string {
            $orders->cancel($order);

            return sprintf('Order #%d has been cancelled.', $order->getId());
        });
    }

    #[Route('/order/{id}/return', name: 'app_account_order_return', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function requestReturn(Request $request, #[CurrentUser] User $user, Order $order, OrderService $orders): Response
    {
        return $this->applyToOwnOrder($request, $user, $order, 'return_order', function () use ($orders, $order, $request): string {
            $orders->requestReturn($order, $request->getPayload()->getString('reason'));

            return sprintf('Return requested for order #%d. We will review it shortly.', $order->getId());
        });
    }

    /** Checks the order is the customer's own and the CSRF token is valid, runs the action, and flashes the result. */
    private function applyToOwnOrder(Request $request, User $user, Order $order, string $tokenPrefix, callable $run): Response
    {
        if ($order->getUser() !== $user) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid($tokenPrefix.$order->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Invalid token. Please try again.');
        } else {
            try {
                $this->addFlash('success', $run());
            } catch (\DomainException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_account');
    }

    /**
     * Deletes the account. Orders must be kept for the store's records,
     * so an account with orders is disabled instead and can no longer log in.
     */
    #[Route('/delete', name: 'app_account_delete', methods: ['POST'])]
    #[IsCsrfTokenValid('delete_account', tokenKey: '_token')]
    public function delete(#[CurrentUser] User $user, OrderRepository $orders, Security $security): Response
    {
        if (!$security->isGranted('ROLE_STAFF')) {
            if ($orders->count(['user' => $user]) > 0) {
                $user->setActive(false);
            } else {
                $this->em->remove($user);
            }
            $this->em->flush();
            $security->logout(false);
            $this->addFlash('success', 'Your account has been deleted.');
        } else {
            $this->addFlash('danger', 'Staff accounts can only be removed by an admin.');
        }

        return $this->redirectToRoute('app_home');
    }
}
