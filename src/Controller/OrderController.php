<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;


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

    // #[Route('', name: 'orders')]
    // public function index(OrderRepository $orderRepository): Response
    // {
    //     $response = new Response($this->twig->render('order/index.html.twig', [
    //         'orders' => $orderRepository->findAll(),
    //     ]));
        
    //     return $response;
    // }

    #[Route('/order/{id}', name: 'orders_view')]
    public function show(OrderRepository $orderRepository, int $id): Response
    {
        $order = $orderRepository->find($id);
        $order->getAmounts($orderRepository);
        return new Response($this->twig->render('order/show.html.twig', [
            'order' => $orderRepository->find($id),
            'amount' => $order->getAmounts($orderRepository),
        ]));
    }

    #[Route('/updateAmount/{id}')]
    public function updateAmount(Request $request, OrderRepository $orderRepository)
    {
        $orderId = $request->get('order-id');
        $order = $orderRepository->find($orderId);
        $amounts = $request->get('products');
        $order->updateAmount($orderRepository, $amounts);

        return $this->redirectToRoute('orders_view', ['id' => $orderId]);
    }

    #[Route('/search', name: 'search_order')]
    public function search(OrderRepository $orderRepository, Request $request): Response
    {
        $name = $request->request->get('orderName');
        if($name === '') {
            return $this->redirectToRoute('orders');
        }
        $result = $orderRepository->createQueryBuilder('o')
        ->where('LOWER(o.client) LIKE LOWER(:name)')
        ->setParameter('name', "%{$name}%")
        ->getQuery()
        ->getResult();
        
        $response = new Response($this->twig->render('product/search.html.twig', [
            'products' => $result,
        ]));
        return $response;
    }

    #[Route('/export/{id}', name: 'orders_export')]
    public function export(OrderRepository $orderRepository, Dompdf $dompdf, int $id): Response
    {
        $order = $orderRepository->find($id);
        if($order === null) {
            //return new RedirectResponse();  TODO: user friendly exception handling
            throw new Exception("Order not found");
        }

        $html = $this->renderView('export/pdf/order.html.twig', [
            'title' => "Order {$id}",
            'order' => $order,
            'amount' => $order->getAmounts($orderRepository),
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("order.pdf", [
            "Attachment" => false
        ]);
        exit(0); //TODO: find a better way
    }

    #[Route('/mail/{id}', name: 'mail')]
    function sendEmail(OrderRepository $orderRepository, int $id): Response
     {
        $order = $orderRepository->find($id);
        $client = $this->entityManager->getRepository(Client::class)->find($order->getClient()->getId());

         $mail = new PHPMailer(true);
 
    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.mail.yahoo.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'viko.crm@yahoo.com';                     //SMTP username
        $mail->Password   = $this->getParameter('app.mail.pass');                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
        $mail->setFrom('viko.crm@yahoo.com', 'Viko CRM');
        $mail->addAddress($client->getEmail(), $client->getName());     //Add a recipient
    
        //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        $html = $this->renderView('export/pdf/order.html.twig', [
            'title' => "Order {$id}",
            'order' => $order,
            'amount' => $order->getAmounts($orderRepository),
        ]);

        $dompdf = new DOMPDF();
        $dompdf->loadHtml($html);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents("Order_{$id}.pdf", $output);
        $mail->addAttachment("Order_{$id}.pdf");
    
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Invoice for order #' . $order->getId();
        $mail->Body    = "This is the invoice for order #<b>{$order->getId()}</b>";
        $mail->AltBody = "This is the invoice for order #{$order->getId()}";
        
        $mail->send();
        } catch (\Exception $e) {
            echo "Message could not be sent.";
        }
        return $this->redirectToRoute('orders');

     }

    #[Route('/add/{clientId}', name:'add_order')]
    public function add(OrderRepository $orderRepository, Request $request, ?int $clientId = null): Response
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
            $order->setUpAmounts($orderRepository);

            return $this->redirectToRoute('orders');
        }
        
        return $this->render('order/add.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    #[Route('/remove/{id}', name: 'delete_order')]
    public function delete(OrderRepository $orderRepository, int $id): Response
    {
        $order = $orderRepository->find($id);
        $this->entityManager->remove($order);
        $this->entityManager->flush();
        return $this->redirectToRoute('orders');
    }

    #[Route('/{page}', name: 'orders')]
    public function index(OrderRepository $orderRepository, int $page=1): Response
    {

        // build the query for the doctrine paginator
        $query = $orderRepository->createQueryBuilder('p')
                            ->orderBy('p.id', 'ASC')
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
        $response = new Response($this->twig->render('order/index.html.twig', [
            'orders' => $paginator,
            'page' => $page,
            'max' => $max,
            'totalPages' => $pagesCount,
        ]));
        
        return $response;
    }
}
