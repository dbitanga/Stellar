<?php

namespace App\Controller;

use App\Dto\ProductFilter;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\ProductCategory;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Security\ProductVoter;
use App\Support\FormErrorsTrait;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/product')]
#[IsGranted('ROLE_STAFF')]
class DashboardProductController extends AbstractController
{
    use FormErrorsTrait;

    #[Route('', name: 'app_dashboard_product_index', methods: ['GET'])]
    public function index(
        ProductRepository $products,
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] ProductFilter $filter = new ProductFilter(),
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('dashboard_product/index.html.twig', [
            'page_title' => 'Products',
            'products' => $products->findFiltered($filter, $user),
            'filter' => $filter,
            'categories' => ProductCategory::cases(),
            'tiers' => Product::TIERS,
            'form' => $this->createForm(ProductType::class, new Product()),
        ]);
    }

    #[Route('/new', name: 'app_dashboard_product_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $product->setCreatedBy($user);
            $em->persist($product);
            $em->flush();
            $this->addFlash('success', sprintf('%s added.', $product->getName()));
        } else {
            $this->flashFormErrors($form);
        }

        return $this->redirectToRoute('app_dashboard_product_index');
    }

    #[Route('/{id}/edit', name: 'app_dashboard_product_edit', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(ProductVoter::MANAGE, 'product')]
    public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', sprintf('%s updated.', $product->getName()));
        } else {
            $this->flashFormErrors($form);
        }

        return $this->redirectToRoute('app_dashboard_product_index');
    }

    #[Route('/{id}/toggle', name: 'app_dashboard_product_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(ProductVoter::MANAGE, 'product')]
    public function toggle(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('toggle_product'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $product->setVisible(!$product->isVisible());
            $em->flush();
            $this->addFlash('success', sprintf('%s is now %s.', $product->getName(), $product->isVisible() ? 'visible' : 'hidden'));
        } else {
            $this->addFlash('danger', 'Invalid token. Please try again.');
        }

        return $this->redirectToRoute('app_dashboard_product_index');
    }

    #[Route('/{id}/delete', name: 'app_dashboard_product_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted(ProductVoter::MANAGE, 'product')]
    public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_product'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Invalid token. Please try again.');

            return $this->redirectToRoute('app_dashboard_product_index');
        }

        try {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', sprintf('%s deleted.', $product->getName()));
        } catch (ForeignKeyConstraintViolationException) {
            $this->addFlash('danger', sprintf('%s is part of existing orders and cannot be deleted. Hide it instead.', $product->getName()));
        }

        return $this->redirectToRoute('app_dashboard_product_index');
    }
}