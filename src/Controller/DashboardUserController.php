<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Support\FormErrorsTrait;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/user')]
#[IsGranted('ROLE_ADMIN')]
class DashboardUserController extends AbstractController
{
    use FormErrorsTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    #[Route('', name: 'app_dashboard_user_index', methods: ['GET'])]
    public function index(UserRepository $users): Response
    {
        return $this->render('dashboard_user/index.html.twig', [
            'page_title' => 'Staff',
            'users' => $users->findStaff(),
            // Password is not required in the shared modal (edits keep it); new() still requires it.
            'form' => $this->createForm(UserType::class, new User(), ['require_password' => false]),
        ]);
    }

    #[Route('/new', name: 'app_dashboard_user_new', methods: ['POST'])]
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyPassword($user, $form);
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash('success', sprintf('%s created.', $user->getUsername()));
        } else {
            $this->flashFormErrors($form);
        }

        return $this->redirectToRoute('app_dashboard_user_index');
    }

    #[Route('/{id}/edit', name: 'app_dashboard_user_edit', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function edit(Request $request, User $user): Response
    {
        if (User::ROLE_USER === $user->getRole()) {
            $this->addFlash('danger', 'Customer accounts are managed by the customers themselves.');

            return $this->redirectToRoute('app_dashboard_customer_index');
        }

        $form = $this->createForm(UserType::class, $user, ['require_password' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->applyPassword($user, $form);
            $this->em->flush();
            $this->addFlash('success', sprintf('%s updated.', $user->getUsername()));
        } else {
            $this->flashFormErrors($form);
        }

        return $this->redirectToRoute('app_dashboard_user_index');
    }

    #[Route('/{id}/toggle', name: 'app_dashboard_user_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggle(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid('toggle_user'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Invalid token. Please try again.');
        } elseif ($user === $this->getUser()) {
            $this->addFlash('danger', 'You cannot disable your own account.');
        } else {
            $user->setActive(!$user->isActive());
            $this->em->flush();
            $this->addFlash('success', sprintf('%s is now %s.', $user->getUsername(), $user->isActive() ? 'active' : 'disabled'));
        }

        return $this->redirectToList($user);
    }

    #[Route('/{id}/delete', name: 'app_dashboard_user_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        if (!$this->isCsrfTokenValid('delete_user'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Invalid token. Please try again.');
        } elseif ($user === $this->getUser()) {
            $this->addFlash('danger', 'You cannot delete your own account.');
        } else {
            try {
                $this->em->remove($user);
                $this->em->flush();
                $this->addFlash('success', sprintf('%s deleted.', $user->getUsername()));
            } catch (ForeignKeyConstraintViolationException) {
                $this->addFlash('danger', sprintf('%s has existing orders and cannot be deleted. Disable it instead.', $user->getUsername()));
            }
        }

        return $this->redirectToList($user);
    }

    /** Toggle and delete are shared by the Staff and Customers pages; go back to the right one. */
    private function redirectToList(User $user): Response
    {
        return $this->redirectToRoute(User::ROLE_USER === $user->getRole() ? 'app_dashboard_customer_index' : 'app_dashboard_user_index');
    }

    private function applyPassword(User $user, FormInterface $form): void
    {
        $plainPassword = $form->get('plainPassword')->getData();
        if ($plainPassword) {
            $user->setPassword($this->hasher->hashPassword($user, $plainPassword));
        }
    }
}
