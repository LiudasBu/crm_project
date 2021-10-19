<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[Route('clients')]
class ClientController extends AbstractController
{
    private $twig;
    private $entityManager;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'clients')]
    public function index(ClientRepository $clientRepository): Response
    {
        $response = new Response($this->twig->render('client/index.html.twig', [
            'clients' => $clientRepository->findAll(),
        ]));
        
        return $response;
    }

    #[Route('/client/{id}', name: 'clients_view')]
    public function show(ClientRepository $clientRepository, int $id): Response
    {
        return new Response($this->twig->render('client/show.html.twig', [
            'client' => $clientRepository->find($id),
        ]));
    }

    #[Route('/add', name:'add_client')]
    public function add(Request $request): Response
    {
        $client = new Client();

        $form = $this->createForm(ClientType::class, $client);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();

            $this->entityManager->persist($client);
            $this->entityManager->flush();

            return $this->redirectToRoute('clients');
        }
        
        return $this->render('client/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/remove/{id}', name: 'delete_client')]
    public function delete(ClientRepository $clientRepository, int $id): Response
    {
        $client = $clientRepository->find($id);
        $this->entityManager->remove($client);
        $this->entityManager->flush();
        return $this->redirectToRoute('clients');
    }
}
