<?php

namespace App\Controller;

use App\Entity\PayPalOrder;
use App\PayPalOrderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Annotation\Route;

class PayPalOrderController extends AbstractController
{
    #[Route('/admin/order', name: 'app_paypal_order_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $orders = $entityManager->getRepository(PayPalOrder::class)->findAll();

        return $this->render('dashboard/order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/admin/order/{paypal_id}', name: 'app_paypal_order_show', methods: ['GET'])]
    public function show(PayPalOrder $order, PayPalOrderHelper $helper): Response
    {
        $products = $helper->getProducts($order->getData());

        $countryName = Countries::getName($order->getData()['payer']['address']['country_code']);

        return $this->render('dashboard/order/show.html.twig', [
            'order' => $order->getData(),
            'products' => $products,
            'country_name' => $countryName
        ]);
    }
}
