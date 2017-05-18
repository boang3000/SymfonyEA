<?php

namespace Arcanys\EasyAppBundle\Service;

use Symfony\Bundle\TwigBundle\TwigEngine;

class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * @var \Symfony\Bundle\TwigBundle\TwigEngine
     */
    protected $templating;
    /**
     * @var array
     */
    protected $config;

    public function __construct(\Swift_Mailer $mailer, TwigEngine $templating, $config)
    {
        $this->mailer     = $mailer;
        $this->templating = $templating;
        $this->config     = $config;
    }

    public function sendInvoiceAlert($emailTo, $invoiceNo)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->config['pending_invoice_subject'])
            ->setFrom($this->config['email_from'])
            ->setTo($emailTo)
            ->setBody(
                $this->templating->render(
                    'ArcanysEasyAppBundle:Email:invoice.html.twig',
                    ['invoiceNo' => $invoiceNo]
                ), 'text/html'
            )
        ;

        $this->mailer->send($message);
    }

}
