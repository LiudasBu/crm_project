<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/orders')]
class OrderController extends AbstractController
{
    private $twig;
    private $entityManager;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'orders')]
    public function index(OrderRepository $orderRepository): Response
    {
        $response = new Response($this->twig->render('order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]));
        
        return $response;
    }

    #[Route('/add', name:'add_order')]
    public function add(Request $request): Response
    {
        $order = new Order();

        $form = $this->createForm(OrderType::class, $order);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $order = $form->getData();

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            return $this->redirectToRoute('orders');
        }
        
        return $this->render('order/add.html.twig', [
            'form' => $form->createView(),
        ]);

    }
}
