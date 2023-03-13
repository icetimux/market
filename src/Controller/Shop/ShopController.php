<?php

namespace App\Controller\Shop;

use App\Cart;
use App\Entity\Product;
use App\EntitySerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function product(Product $product, Cart $cart, Request $request): Response
    {
        $amountInCart = $cart->getCart($request)[$product->getId()] ?? 0;

        if ($product->getMaxAllowedPerOrder() > $product->getStock()) {
            $maxAllowed = $product->getStock() - $amountInCart;
        } else {
            $maxAllowed = $product->getMaxAllowedPerOrder() - $amountInCart;
        }

        return $this->render('shop/product.html.twig', [
            'max_allowed' => $maxAllowed,
            'product' => $product
        ]);
    }

    #[Route('/cart', name: 'app_shop_cart')]
    public function cart(Request $request, Cart $cart, EntityManagerInterface $entityManager, EntitySerializer $serializer): Response
    {
        $cart = $cart->getCart($request);
        $serializedProducts = [];
        $subtotal = 0.0;

        foreach ($cart as $productId => $quantity) {
            $product = $entityManager->getRepository(Product::class)->find($productId);
            $serializedProduct = json_decode($serializer->serialize($product, 'json'), true);

            $serializedProduct['quantity'] = $quantity;

            if ($product->getMaxAllowedPerOrder() > $product->getStock()) {
                $maxAllowed = $product->getStock();
            } else {
                $maxAllowed = $product->getMaxAllowedPerOrder();
            }
            $serializedProduct['max_allowed'] = $maxAllowed;
            $serializedProduct['images'] = $product->getImagesArray();

            $serializedProducts[] = $serializedProduct;
            $subtotal += $product->getPrice() * $quantity;
        }

        return $this->render('shop/cart.html.twig', [
            'products' => array_filter($serializedProducts),
            'subtotal' => $subtotal
        ]);
    }

    #[Route('/success', name: 'order_success')]
    public function success(Request $request): Response
    {
        $request->getSession()->clear();
        return $this->render('shop/success.html.twig');
    }
}
