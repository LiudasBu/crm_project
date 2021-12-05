<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class MailerController extends AbstractController
{
 //         // $email = new \SendGrid\Mail\Mail(); 
 //         // $email->setFrom("test@example.com", "Example User");
 //         // $email->setSubject("Sending with SendGrid is Fun");
 //         // $email->addTo("liudas.bucys@gmail.com", "Example User");
 //         // $email->addContent("text/plain", "and easy to do anywhere, even with PHP");
 //         // $email->addContent(
 //         //     "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
 //         // );
 //         // $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
 //         // try {
 //         //     $response = $sendgrid->send($email);
 //         //     dd($response);
 //         //     // print $response->statusCode() . "\n";
 //         //     // print_r($response->headers());
 //         //     // print $response->body() . "\n";
 //         // } catch (Exception $e) {
 //         //     echo 'Caught exception: '. $e->getMessage() ."\n";
 //         // }
 //         // return $this->render('dashboard/index.html.twig', [
 //         //     'controller_name' => 'DashboardController',
 //         // ]);
         // $email = (new Email())
         //     // ->from('')
         //     // ->to('')
         //     ->subject('Testing')
         //     ->text('Testing the mail')
         //     ->html('<p>See Twig integration for better HTML integration!</p>');
 
         // $mailer->send($email);
         // return;
}

