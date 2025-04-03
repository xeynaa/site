<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/users', name: 'admin_user')]
    public function listUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/delete/{id}', name: 'admin_user_delete')]
    public function deleteUser(User $user, EntityManagerInterface $em, CartRepository $cartRepo): Response
    {
        $currentUser = $this->getUser();

        // Ne pas supprimer un super-admin ni soi-même
        if ($user->getId() === $currentUser->getId() || in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $this->addFlash('error', 'Impossible de supprimer cet utilisateur.');
            return $this->redirectToRoute('admin_user');
        }

        // Supprimer les produits de son panier d'abord
        foreach ($cartRepo->findBy(['user' => $user]) as $item) {
            $em->remove($item);
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

        return $this->redirectToRoute('admin_user');
    }
}
