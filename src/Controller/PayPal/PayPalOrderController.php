<?php

namespace App\Controller\PayPal;

use App\Cart;
use App\Entity\PayPalOrder;
use App\PayPalOrderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PayPalOrderController extends AbstractController
{
    #[Route('paypal/create-order', name: 'paypal_create_order')]
    public function create(Request $request, Cart $cartService): Response
    {
        $shipping = 10; // standard 10 EUR shipping fee
        $result = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'items' => [
                    ]
                ]
            ]
        ];

        $totals = [];

        $cart = $cartService->getCartWithNames($request);

        foreach ($cart as $name => $data) {
            $result['purchase_units'][0]['items'][] = [
                'name' => $name,
                'quantity' => $data['quantity'],
                'unit_amount' => [
                    'currency_code' => 'EUR',
                    'value' => round($data['price'], 2),
                ]
            ];

            $totals[$name] = round(($data['quantity'] * $data['price']), 2);
        }

        $totalsWithShipping = $totals;
        $totalsWithShipping['shipping'] = $shipping;

        $result['purchase_units'][0]['amount'] = [
            'currency_code' => 'EUR',
            'value' => round(array_sum(array_values($totalsWithShipping)), 2),
            'breakdown' => [
                'item_total' => [
                    'currency_code' => 'EUR',
                    'value' => round(array_sum(array_values($totals)), 2),
                ],
                'shipping' => [
                    'currency_code' => 'EUR',
                    'value' => $shipping
                ]
            ],
        ];

        return new JsonResponse($result, 200);
    }

    #[Route('paypal/confirm-order', name: 'paypal_confirm_order', methods: ['POST'])]
    public function confirm(Request $request, PayPalOrderHelper $helper, EntityManagerInterface $entityManager): Response
    {
        $orderData = json_decode($request->getContent(), true);

        $order = new PayPalOrder();
        $order->setPaypalId($orderData['id']);
        $order->setData($orderData);
        $order->setFinalized(false);
        $order->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($order);
        $entityManager->flush();

        $helper->sendOrderConfirmationEmail($orderData);

        return new JsonResponse(null, 200);
    }
}
