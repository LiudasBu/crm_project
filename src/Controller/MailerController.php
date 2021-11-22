<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;


class MailerController extends AbstractController
{
    #[Route('/mail', name: 'mail')]
   function sendEmail(MailerInterface $mailer): void
    {
        $email = (new Email())
            // ->from('')
            // ->to('')
            ->subject('Testing')
            ->text('Testing the mail')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);
        return;
    }
}