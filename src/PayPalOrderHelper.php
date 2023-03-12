<?php

namespace App;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class PayPalOrderHelper
{
    public function __construct(private EntityManagerInterface $entityManager, private EntitySerializer $serializer)
    {
    }

    public function getProducts(array $order): array
    {
        $products = [];

        foreach ($order['purchase_units'][0]['items'] as $lineItem) {
            $product = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => $lineItem['name']]);
            $product = json_decode($this->serializer->serialize($product, 'json'), true);

            $product['quantity'] = $lineItem['quantity'];
            $product['subtotal'] = round((float)$lineItem['unit_amount']['value'] * $lineItem['quantity'], 2);

            $products[] = $product;
        }

        return $products;
    }
}
