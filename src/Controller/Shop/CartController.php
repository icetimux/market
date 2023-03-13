<?php

namespace App\Controller\Shop;

use App\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{

    public function __construct(private Cart $cart)
    {
    }

    #[Route('/cart/add', name: 'app_cart_add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $cart = $this->cart->getCart($request);
        $productId = (int)$request->get('product_id');
        $amount = (int)$request->get('amount');

        $amountAlreadyInCart = $cart[$productId] ?? 0;

        $newAmount = $amountAlreadyInCart + $amount;

        $cart[$productId] = $newAmount;

        $this->cart->setCart($request, $cart);

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/cart/update', name: 'app_cart_update', methods: ['POST'])]
    public function update(Request $request): Response
    {
        $cart = $this->cart->getCart($request);
        $productId = (int)$request->get('product_id');
        $amount = (int)$request->get('amount');

        $newAmount = $amount;

        if ($newAmount < 0) {
            $newAmount = 0;
        }

        $cart[$productId] = $newAmount;

        $this->cart->setCart($request, $cart);

        return $this->redirect($request->headers->get('referer'));
    }


    #[Route('/cart/remove', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(Request $request): Response
    {
        $cart = $this->cart->getCart($request);
        $productId = (int)$request->get('product_id');

        $newCart = array_filter($cart, function ($key) use ($productId) {
            return $key !== $productId;
        }, ARRAY_FILTER_USE_KEY);

        $this->cart->setCart($request, $newCart);

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @deprecated
     * @param Request $request
     * @return Response
     */
    public function count(Request $request): Response
    {
        $cart = $this->cart->getCart($request);

        return new Response(count(array_keys($cart)), 200);
    }
}
