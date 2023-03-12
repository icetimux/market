<?php

namespace App\Controller;

use App\Cart;
use App\Entity\PayPalOrder;
use App\PayPalOrderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Email;
use Symfony\Component\Intl\Countries;

class PayPalController extends AbstractController
{
    public function __construct(private PayPalOrderHelper $orderHelper, private MailerInterface $mailer, private EntityManagerInterface $entityManager)
    {
    }

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
    public function confirm(Request $request): Response
    {
        $orderData = json_decode($request->getContent(), true);

        $order = new PayPalOrder();
        $order->setPaypalId($orderData['id']);
        $order->setData($orderData);
        $order->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $this->sendOrderConfirmationEmail($orderData);

        return new JsonResponse(null, 200);
    }

    private function sendOrderConfirmationEmail(array $order)
    {
        $customerEmail = $order['payer']['email_address'];

        $email = (new Email())
            ->from($this->getParameter('site_email'))
            ->subject('Order #'.$order['id']. ' confirmed')
            ->to($customerEmail)
            ->bcc($this->getParameter('site_email'))
            ->html($this->getEmailHtml($order));

        $this->mailer->send($email);
    }

    private function getEmailHtml(array $order): string
    {
        $products = $this->orderHelper->getProducts($order);

        $countryName = Countries::getName($order['payer']['address']['country_code']);

        return $this->renderView('emails/order_confirmation.html.twig', [
            'site_email' => $this->getParameter('site_email'),
            'site_name' => $this->getParameter('site_name'),
            'order' => $order,
            'products' => $products,
            'country_name' => $countryName
        ]);
    }
}
