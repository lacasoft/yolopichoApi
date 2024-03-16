<?php

namespace Yolopicho\Utilities;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure() {
        $this->mailer->isSMTP();
        $this->mailer->Host = getenv('EMAIL_HOST');
        $this->mailer->SMTPAuth = getenv('EMAIL_AUTH');
        $this->mailer->Username = getenv('EMAIL_USER');
        $this->mailer->Password = getenv('EMAIL_PASS');
        $this->mailer->SMTPSecure = getenv('EMAIL_SECURE');
        $this->mailer->Port = getenv('EMAIL_PORT');
        $this->mailer->Timeout = 30;
    }

    public function send($to, $toName, $subject, $body, $isHtml = true) {
        try {
            $fromMail = getenv('EMAIL_USER');
            $fromName = getenv('APP_NAME');
            $this->mailer->setFrom($fromMail, $fromName);
            $this->mailer->addAddress($to, $toName);
            $this->mailer->isHTML($isHtml);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            throw new \Exception('Ocurrió un error: ' . $e->getMessage(), 400);
            return false;
        }
    }
}