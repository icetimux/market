<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class Cart
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function getCart(Request $request): array
    {
        $session = $request->getSession();
        return $session->get('cart', []);
    }

    public function setCart(Request $request, array $cart)
    {
        $session = $request->getSession();
        return $session->set('cart', $cart);
    }

    public function getCartWithNames(Request $request): array
    {
        $result = [];
        $cart = $this->getCart($request);

        foreach ($cart as $id => $quantity) {
            $product = $this->entityManager->getRepository(Product::class)->find($id);
            if ($product) {
                $result[$product->getName()] = [
                    'quantity' => $quantity,
                    'price' => (string)$product->getPrice()
                ];
            }
        }

        return $result;
    }
}