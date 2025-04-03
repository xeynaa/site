<?php

namespace App\Controller\Client;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/client/products', name: 'client_products')]
    public function indexClentProduit(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('client/product_list.html.twig', [
            'products' => $products,
        ]);
    }

}

