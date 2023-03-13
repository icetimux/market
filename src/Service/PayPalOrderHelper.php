<?php

namespace App\Service;

use App\Entity\PayPalOrder;
use App\Entity\Product;
use App\EntitySerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class PayPalOrderHelper
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EntitySerializer $serializer,
        private MailerInterface $mailer,
        private ParameterBagInterface $params,
        private Environment $twig
    )
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

    public function sendOrderConfirmationEmail(array $order)
    {
        $customerEmail = $order['payer']['email_address'];

        $email = (new Email())
            ->from($this->params->get('site_email'))
            ->subject('Order #'.$order['id']. ' confirmed')
            ->to($customerEmail)
            ->bcc($this->params->get('site_email'))
            ->html($this->getOrderConfirmationEmailHtml($order));

        $this->mailer->send($email);
    }

    public function getOrderConfirmationEmailHtml(array $order): string
    {
        $products = $this->getProducts($order);

        $countryName = Countries::getName($order['payer']['address']['country_code']);

        return $this->twig->render('emails/order_confirmation.html.twig', [
            'site_email' => $this->params->get('site_email'),
            'site_name' => $this->params->get('site_name'),
            'order' => $order,
            'products' => $products,
            'country_name' => $countryName
        ]);
    }

    public function sendShipmentConfirmationEmail(PayPalOrder $order)
    {
        $orderData = $order->getData();
        $customerEmail = $orderData['payer']['email_address'];

        $email = (new Email())
            ->from($this->params->get('site_email'))
            ->subject('Order #'.$orderData['id']. ' confirmed')
            ->to($customerEmail)
            ->bcc($this->params->get('site_email'))
            ->html($this->getShipmentConfirmationEmailHtml($order));

        $this->mailer->send($email);
    }

    public function getShipmentConfirmationEmailHtml(PayPalOrder $order): string
    {
        return $this->twig->render('emails/shipment_confirmation.html.twig', [
            'site_email' => $this->params->get('site_email'),
            'site_name' => $this->params->get('site_name'),
            'order' => $order->getData(),
            'tracking_number' => $order->getTrackingNumber(),
        ]);
    }
}
