<?php

namespace App\Controller\Client;

use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/client', 'client_')]
final class ClientController extends AbstractController
{
    #[Route('profile', name: 'profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a bien été mis à jour.');

            // Redirection selon le rôle
            if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('app_accueil');
            } else {
                return $this->redirectToRoute('client_products');
            }
        }

        return $this->render('client/profile.html.twig', [
            'ProfileForm' => $form->createView(),
        ]);
    }
    #[Route('products', name: 'products')]
    public function products(ProductRepository $productRepository, CartRepository $cartRepository): Response
    {
        $user = $this->getUser();
        $products = $productRepository->findAll();

        // Récupère les quantités du panier pour cet utilisateur
        $cartItems = $cartRepository->findBy(['user' => $user]);
        $quantitiesInCart = [];
        foreach ($cartItems as $item) {
            $quantitiesInCart[$item->getProduct()->getId()] = $item->getQuantity();
        }

        return $this->render('client/products.html.twig', [
            'products' => $products,
            'quantitiesInCart' => $quantitiesInCart,
        ]);
    }

}
