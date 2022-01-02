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

    #[Route('/edit/{id}', name:'edit_client')]
    public function edit(Request $request, ClientRepository $clientRepository, int $id): Response
    {
        $client = $clientRepository->find($id);

        $form = $this->createForm(ClientType::class, $client);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();

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

    #[Route('/massMail', name: 'client_mass_mail')]
    public function massMail(Request $request) : Response
    {
        $ids = $request->query->all();
        return $this->render('client/mail.html.twig', [
            'ids' => implode(',', $ids),
        ]);
    }

    #[Route('/sendMail', name: 'client_mass_mail_send')]
    public function sendMail(ClientRepository $clientRepository, Request $request) : Response
    {
        $ids = $request->query->get('ids');
        $emailText = $request->query->get('emailText');
        $password = $this->getParameter('app.mail.pass');
        
        foreach(explode(',', $ids) as $clientId) {
            $client = $clientRepository->find($clientId);
            $client->sendMail($emailText, $password);
        }

        return $this->redirectToRoute('clients');
    }

    #[Route('/search', name: 'search_client')]
    public function search(ClientRepository $clientRepository, Request $request): Response
    {
        $name = $request->request->get('clientName');
        if($name === '') {
            return $this->redirectToRoute('clients');
        }
        $result = $clientRepository->createQueryBuilder('c')
        ->where('LOWER(c.name) LIKE LOWER(:name)')
        ->setParameter('name', "%{$name}%")
        ->getQuery()
        ->getResult();
        
        $response = new Response($this->twig->render('client/search.html.twig', [
            'clients' => $result,
        ]));
        return $response;
    }

    
    #[Route('/{page}', name: 'clients')]
    public function index(ClientRepository $clientRepository, int $page=1): Response
    {

        // build the query for the doctrine paginator
        $query = $clientRepository->createQueryBuilder('u')
                            ->orderBy('u.id', 'ASC')
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
        $response = new Response($this->twig->render('client/index.html.twig', [
            'clients' => $paginator,
            'page' => $page,
            'max' => $max,
            'totalPages' => $pagesCount,
        ]));
        
        return $response;
    }

}
