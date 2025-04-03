<?php

namespace App\Controller\Client;

use App\Entity\Cart;
use App\Entity\Product;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client/cart')]
#[IsGranted('ROLE_CLIENT')]
class CartController extends AbstractController
{
    #[Route('', name: 'client_cart')]
    public function view(CartRepository $cartRepo): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $cartItems = $cartRepo->findBy(['user' => $user]);

        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item->getQuantity() * $item->getProduct()->getPrice();
        }

        return $this->render('client/cart.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }


    #[Route('/add/{id}', name: 'client_cart_add')]
    public function add(Product $product, EntityManagerInterface $em, CartRepository $cartRepo): Response
    {
        $user = $this->getUser();
        $existingCart = $cartRepo->findOneBy(['user' => $user, 'product' => $product]);

        if ($existingCart) {
            $existingCart->setQuantity($existingCart->getQuantity() + 1);
        } else {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setProduct($product);
            $cart->setQuantity(1);
            $em->persist($cart);
        }

        $em->flush();

        return $this->redirectToRoute('client_cart');
    }

    #[Route('/remove/{id}', name: 'client_cart_remove')]
    public function remove(Cart $cart, EntityManagerInterface $em): Response
    {
        $em->remove($cart);
        $em->flush();

        return $this->redirectToRoute('client_cart');
    }

    #[Route('/clear', name: 'client_cart_clear')]
    public function clear(CartRepository $cartRepo, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $items = $cartRepo->findBy(['user' => $user]);

        foreach ($items as $item) {
            $em->remove($item);
        }

        $em->flush();

        return $this->redirectToRoute('client_cart');
    }
    #[Route('update/{id}', name: 'client_cart_update', methods: ['POST'])]
    public function updateCart(
        int $id,
        Request $request,
        ProductRepository $productRepository,
        CartRepository $cartRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('Utilisateur non connecté ou invalide.');
        }

        $product = $productRepository->find($id);
        $quantity = (int) $request->request->get('quantity');

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $cartItem = $cartRepository->findOneBy(['user' => $user, 'product' => $product]);

        if (!$cartItem && $quantity > 0) {
            $cartItem = new Cart();
            $cartItem->setUser($user);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $product->setStock($product->getStock() - $quantity);
            $entityManager->persist($cartItem);
        } elseif ($cartItem) {
            $delta = $quantity - $cartItem->getQuantity();
            $product->setStock($product->getStock() - $delta);

            if ($quantity <= 0) {
                $entityManager->remove($cartItem);
            } else {
                $cartItem->setQuantity($quantity);
            }
        }

        $entityManager->flush();

        return $this->redirectToRoute('client_products');
    }

    #[Route('/order', name: 'client_cart_order')]
    public function order(CartRepository $cartRepo, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $items = $cartRepo->findBy(['user' => $user]);

        foreach ($items as $item) {
            $em->remove($item);
        }

        $em->flush();
        $this->addFlash('success', 'Votre commande a été enregistrée.');
        return $this->redirectToRoute('client_cart');
    }

}
