<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Support\FormErrorsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/product')]
class DashboardProductController extends AbstractController
{
    use FormErrorsTrait;

    #[Route('', name: 'app_dashboard_product_index', methods: ['GET'])]
    public function index(ProductRepository $products): Response
    {
        return $this->render('dashboard_product/index.html.twig', [
            'page_title' => 'Products',
            'products' => $products->findAllWithCategory(),
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
            $em->persist($product);
            $em->flush();
            $this->addFlash('success', sprintf('%s added.', $product->getName()));
        } else {
            $this->flashFormErrors($form);
        }

        return $this->redirectToRoute('app_dashboard_product_index');
    }

    #[Route('/{id}/edit', name: 'app_dashboard_product_edit', requirements: ['id' => '\d+'], methods: ['POST'])]
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
    public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_product'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', sprintf('%s deleted.', $product->getName()));
        } else {
            $this->addFlash('danger', 'Invalid token. Please try again.');
        }

        return $this->redirectToRoute('app_dashboard_product_index');
    }
}