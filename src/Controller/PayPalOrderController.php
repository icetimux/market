<?php

namespace App\Controller;

use App\Entity\PayPalOrder;
use App\PayPalOrderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
            'order' => $order,
            'orderData' => $order->getData(),
            'products' => $products,
            'country_name' => $countryName
        ]);
    }

    #[Route('/admin/order/{paypal_id}', name: 'app_paypal_order_update', methods: ['PUT'])]
    public function update(Request $request, PayPalOrder $order,  EntityManagerInterface $entityManager): Response
    {
        $order->setTrackingNumber($request->get('tracking_number'));
        $entityManager->persist($order);
        $entityManager->flush();

        $this->addFlash('success', 'Tracking number updated');

        return $this->redirectToRoute('app_paypal_order_show', ['paypal_id' => $order->getPaypalId()]);
    }

    #[Route('/admin/order/finalize', name: 'app_paypal_order_finalize', methods: ['POST'])]
    public function finalize(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $payPalId = $request->get('paypal_id');
        $order = $entityManager->getRepository(PayPalOrder::class)->findOneBy(['paypal_id' => $payPalId]);

        $redirectRoute = $this->redirectToRoute('app_paypal_order_show', ['paypal_id' => $order->getPaypalId()]);

        if (!$order->getTrackingNumber()) {
            $this->addFlash('error', 'Can not finalize order without tracking number');
            return $redirectRoute;
        }

        if ($order->isFinalized()) {
            $this->addFlash('error', 'Order already finalized');
            return $redirectRoute;
        }

        $order->setFinalized(true);
        $entityManager->persist($order);
        $entityManager->flush();

        $this->addFlash('success', 'Order finalized');

        // send email
        $orderData = $order->getData();
        $customerEmail = $orderData['payer']['email_address'];

        $email = (new Email())
            ->from($this->getParameter('site_email'))
            ->subject('Order #'.$orderData['id']. ' confirmed')
            ->to($customerEmail)
            ->bcc($this->getParameter('site_email'))
            ->html($this->getEmailHtml($order));

        $mailer->send($email);

        return $redirectRoute;
    }

    private function getEmailHtml(PayPalOrder $order): string
    {
        return $this->renderView('emails/shipment_confirmation.html.twig', [
            'site_email' => $this->getParameter('site_email'),
            'site_name' => $this->getParameter('site_name'),
            'order' => $order->getData(),
            'tracking_number' => $order->getTrackingNumber(),
        ]);
    }
}
