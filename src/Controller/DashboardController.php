<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;



class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(Security $security, EntityManagerInterface $entityManager): Response
    {
        $userCount = $entityManager->getRepository(User::class)->getCount();
        $clientCount = $entityManager->getRepository(Client::class)->getCount();
        $productCount = $entityManager->getRepository(Product::class)->getCount();
        $orderCount = $entityManager->getRepository(Order::class)->getCount();


        $user = $security->getUser();
        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'controller_name' => 'DashboardController',
            'userCount' => $userCount,
            'clientCount' => $clientCount,
            'orderCount' => $orderCount,
            'productCount' => $productCount,
        ]);
    }
}
