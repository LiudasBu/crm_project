<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/products')]
class ProductController extends AbstractController
{
    private $twig;
    private $entityManager;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'products')]
    public function index(ProductRepository $productRepository): Response
    {
        $response = new Response($this->twig->render('product/index.html.twig', [
            'products' => $productRepository->findBy([
                'isDeleted' => false,
            ]),
        ]));
        
        return $response;
    }

    #[Route('/product/{id}', name: 'products_view')]
    public function show(ProductRepository $productRepository, int $id): Response
    {
        return new Response($this->twig->render('product/show.html.twig', [
            'product' => $productRepository->find($id),
        ]));
    }

    #[Route('/add', name:'add_product')]
    public function add(Request $request): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('products');
        }
        
        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    #[Route('/remove/{id}', name: 'delete_product')]
    public function delete(ProductRepository $productRepository, int $id): Response
    {
        $product = $productRepository->find($id);
        $product->setIsDeleted(true);
        $this->entityManager->flush();
        return $this->redirectToRoute('products');
    }
}
