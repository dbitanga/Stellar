<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\ProductCategory;
use App\Form\RegistrationType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/** The public store pages: home, products and sign-up. */
class StoreController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('store/home.html.twig');
    }

    #[Route('/products', name: 'app_products', methods: ['GET'])]
    public function products(ProductRepository $products): Response
    {
        $byCategory = [];
        foreach ([ProductCategory::Workstation, ProductCategory::Gaming, ProductCategory::Personal] as $category) {
            $byCategory[$category->name] = [];
        }
        foreach ($products->findForStore() as $product) {
            $byCategory[$product->getCategory()->name][] = $product;
        }

        return $this->render('store/products.html.twig', ['categories' => $byCategory]);
    }

    #[Route('/signup', name: 'app_signup', methods: ['GET', 'POST'])]
    public function signup(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = (new User())->setRole(User::ROLE_USER);
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $em->persist($user);
            $em->flush();

            $this->addFlash('signed_up', $user->getUsername());

            return $this->redirectToRoute('app_signup_done');
        }

        return $this->render('store/signup.html.twig', ['form' => $form], new Response(status: $form->isSubmitted() ? 422 : 200));
    }

    #[Route('/signup/done', name: 'app_signup_done', methods: ['GET'])]
    public function signupDone(Request $request): Response
    {
        $username = $request->getSession()->getFlashBag()->get('signed_up')[0] ?? null;
        if (null === $username) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('store/signup_done.html.twig', ['username' => $username]);
    }
}
