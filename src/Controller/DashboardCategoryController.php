<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Support\FormErrorsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/category')]
class DashboardCategoryController extends AbstractController
{
    use FormErrorsTrait;

    #[Route('', name: 'app_dashboard_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categories): Response
    {
        return $this->render('dashboard_category/index.html.twig', [
            'page_title' => 'Categories',
            'categories' => $categories->findBy([], ['name' => 'ASC']),
            'form' => $this->createForm(CategoryType::class, new Category()),
        ]);
    }

    #[Route('/new', name: 'app_dashboard_category_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', sprintf('%s added.', $category->getName()));
        } else {
            $this->flashFormErrors($form);
        }

        return $this->redirectToRoute('app_dashboard_category_index');
    }

    #[Route('/{id}/edit', name: 'app_dashboard_category_edit', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', sprintf('%s updated.', $category->getName()));
        } else {
            $this->flashFormErrors($form);
        }

        return $this->redirectToRoute('app_dashboard_category_index');
    }

    #[Route('/{id}/delete', name: 'app_dashboard_category_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_category'.$category->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Invalid token. Please try again.');
        } elseif ($category->getProducts()->count() > 0) {
            $this->addFlash('danger', sprintf('%s still has products. Move or delete them first.', $category->getName()));
        } else {
            $em->remove($category);
            $em->flush();
            $this->addFlash('success', sprintf('%s deleted.', $category->getName()));
        }

        return $this->redirectToRoute('app_dashboard_category_index');
    }
}