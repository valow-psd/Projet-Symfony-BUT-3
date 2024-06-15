<?php
// src/Service/EmailService.php
namespace App\Service;

use Mailjet\Client;
use Mailjet\Resources;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EmailService
{
    private $mailjet;
    private $senderEmail;
    private $senderName;

    public function __construct(ParameterBagInterface $params)
    {
        $apiKey = $params->get('mailjet_api_key');
        $apiSecret = $params->get('mailjet_api_secret');
        $this->senderEmail = $params->get('mailjet_sender_email');
        $this->senderName = $params->get('mailjet_sender_name');

        $this->mailjet = new Client($apiKey, $apiSecret, true, ['version' => 'v3.1']);
    }

    public function sendEmail(string $to, string $subject, string $content): void
    {
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $this->senderEmail,
                        'Name' => $this->senderName,
                    ],
                    'To' => [
                        [
                            'Email' => $to,
                        ]
                    ],
                    'Subject' => $subject,
                    'HTMLPart' => $content,
                ]
            ]
        ];

        $response = $this->mailjet->post(Resources::$Email, ['body' => $body]);

        if (!$response->success()) {
            throw new \Exception('Email could not be sent: ' . $response->getReasonPhrase());
        }
    }

    public function sendPaymentConfirmationEmail(string $to, string $eventTitle): void
    {
        $subject = 'Payment Confirmation';
        $content = sprintf('You have successfully paid for the event: %s', $eventTitle);
        $this->sendEmail($to, $subject, $content);
    }
}
