<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('clients')]
class ClientController extends AbstractController
{
    #[Route('', name: 'clients')]
    public function index(): Response
    {
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    #[Route('/add', name:'add_client')]
    public function add(): Response
    {
        return new Response();
    }
}
