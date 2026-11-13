<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\ProfileType;
use App\Support\FormErrorsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/profile')]
#[IsGranted('ROLE_STAFF')]
class ProfileController extends AbstractController
{
    use FormErrorsTrait;

    #[Route('', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'page_title' => 'My Profile',
            'form' => $this->createForm(ChangePasswordType::class, null, [
                'action' => $this->generateUrl('app_profile_password'),
            ]),
            'profile_form' => $this->createForm(ProfileType::class, $this->getUser(), [
                'action' => $this->generateUrl('app_profile_edit'),
            ]),
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit', methods: ['POST'])]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Your profile has been updated.');
        } else {
            $this->flashFormErrors($form);
            // Throw away the rejected values so they can't be saved by a later flush.
            $em->refresh($user);
        }

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/password', name: 'app_profile_password', methods: ['POST'])]
    public function changePassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('newPassword')->getData()));
            $em->flush();
            $this->addFlash('success', 'Your password has been changed.');
        } else {
            $this->flashFormErrors($form);
        }

        return $this->redirectToRoute('app_profile');
    }
}
