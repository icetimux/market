<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;

class Cart
{
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
}