<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    #[Route('/admin/product', name: 'app_product_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('dashboard/product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/admin/product/create', name: 'app_product_create', methods: ['GET'])]
    public function create(): Response
    {
        return $this->render('dashboard/product/create.html.twig');
    }

    #[Route('/admin/product', name: 'app_product_store', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // create instance
        $product = new Product();
        $product->setName($request->get('name'));
        $product->setLabel($request->get('label'));
        $product->setPrice($request->get('price'));
        $product->setDescription($request->get('description'));
        $product->setStock((int)$request->get('stock') ?? null);
        $product->setImages($request->get('images'));
        $product->setMaxAllowedPerOrder($request->get('max_allowed_per_order'));

        // check errors
        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getpropertyPath() . ': ' . $error->getMessage());
            }
            return $this->redirectToRoute('app_product_index');
        }

        // persist
        $entityManager->persist($product);
        $entityManager->flush();

        $this->addFlash('success', 'Product saved');
        return $this->redirectToRoute('app_product_index');
    }

    #[Route('/admin/product/{product}/edit', name: 'app_product_edit', methods: ['GET'])]
    public function edit(Product $product): Response
    {
        return $this->render('dashboard/product/edit.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('/admin/product/{product}', name: 'app_product_update', methods: ['PUT'])]
    public function update(Request $request, Product $product, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $product->setName($request->get('name'));
        $product->setLabel($request->get('label'));
        $product->setPrice($request->get('price'));
        $product->setDescription($request->get('description'));
        $product->setStock($request->get('stock'));
        $product->setImages($request->get('images'));
        $product->setName($request->get('name'));
        $product->setMaxAllowedPerOrder($request->get('max_allowed_per_order'));

        // check errors
        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getpropertyPath() . ': ' . $error->getMessage());
            }

            return $this->redirectToRoute('app_product_edit', ['product' => $product->getId()]);
        }

        // persist
        $entityManager->persist($product);
        $entityManager->flush();

        $this->addFlash('success', 'Product updated');
        return $this->redirectToRoute('app_product_index');
    }

    #[Route('/admin/product/{product}', name: 'app_product_delete', methods: ['DELETE'])]
    public function delete(Product $product, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', 'Product deleted');
        return $this->redirectToRoute('app_product_index');
    }
}
