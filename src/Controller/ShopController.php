<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/', name: 'app_shop')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('shop/index.html.twig');
    }

    #[Route('/products', name: 'app_shop_products')]
    public function products(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('shop/products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{label}', name: 'app_shop_product')]
    public function product(Product $product): Response
    {
        return $this->render('shop/product.html.twig', [
            'product' => $product,
        ]);
    }
}
