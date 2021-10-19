<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Client;
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

    #[Route('/order/{id}', name: 'orders_view')]
    public function show(OrderRepository $orderRepository, int $id): Response
    {
        return new Response($this->twig->render('client/show.html.twig', [
            'order' => $orderRepository->find($id),
        ]));
    }

    #[Route('/add/{clientId}', name:'add_order')]
    public function add(Request $request, ?int $clientId = null): Response
    {
        $order = new Order();

        $form = $this->createForm(OrderType::class, $order);
        if($clientId) {
            $client = $this->entityManager->getRepository(Client::class)->find($clientId);
            $order->setClient($client);
            $form->setData($order);
        }

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
