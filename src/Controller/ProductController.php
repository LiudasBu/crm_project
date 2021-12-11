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

    // #[Route('', name: 'products')]
    // public function index(ProductRepository $productRepository): Response
    // {
    //     $response = new Response($this->twig->render('product/index.html.twig', [
    //         'products' => $productRepository->findBy([
    //             'isDeleted' => false,
    //         ]),
    //     ]));
        
    //     return $response;
    // }

    #[Route('/product/{id}', name: 'products_view')]
    public function show(ProductRepository $productRepository, int $id): Response
    {
        return new Response($this->twig->render('product/show.html.twig', [
            'product' => $productRepository->find($id),
        ]));
    }

    #[Route('/edit/{id}', name:'edit_product')]
    public function edit(Request $request, ProductRepository $productRepository, int $id): Response
    {
        $product = $productRepository->find($id);

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $this->entityManager->flush();

            return $this->redirectToRoute('products');
        }
        
        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
        ]);

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

    #[Route('/search', name: 'search_product')]
    public function search(ProductRepository $productRepository, Request $request): Response
    {
        $name = $request->request->get('productName');
        if($name === '') {
            return $this->redirectToRoute('products');
        }
        $result = $productRepository->createQueryBuilder('p')
        ->where('LOWER(p.name) LIKE LOWER(:name)')
        ->andWhere('p.isDeleted = FALSE')
        ->setParameter('name', "%{$name}%")
        ->getQuery()
        ->getResult();
        
        $response = new Response($this->twig->render('product/search.html.twig', [
            'products' => $result,
        ]));
        return $response;
    }

    #[Route('/{page}', name: 'products')]
    public function index(ProductRepository $productRepository, int $page=1): Response
    {

        // build the query for the doctrine paginator
        $query = $productRepository->createQueryBuilder('p')
                            ->orderBy('p.id', 'ASC')
                            ->where('p.isDeleted = FALSE')
                            ->getQuery();

        //set page size
        $pageSize = $this->getParameter('page.size') ?? 10;

        // load doctrine Paginator
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        // you can get total items
        $totalItems = count($paginator);

        // get total pages
        $pagesCount = ceil($totalItems / $pageSize);

        // now get one page's items:
        $paginator
            ->getQuery()
            ->setFirstResult($pageSize * ($page-1)) // set the offset
            ->setMaxResults($pageSize); // set the limit

        ($page == $pagesCount) ? $max = false : $max = true;
        $response = new Response($this->twig->render('product/index.html.twig', [
            'products' => $paginator,
            'page' => $page,
            'max' => $max,
            'totalPages' => $pagesCount,
        ]));
        
        return $response;
    }
}
