<?php

namespace Arcanys\EasyAppBundle\Service;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\Container;
use Arcanys\EasyAppBundle\Service\Mailer;

class LogoutListener implements LogoutSuccessHandlerInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;
    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    private $security;
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    private $router;
    /**
     * @var \Arcanys\EasyAppBundle\Service\Mailer
     */
    private $mailer;

    public function __construct(SecurityContext $security, Router $router, Container $container, Mailer $mailer)
    {
        $this->security = $security;
        $this->router = $router;
        $this->container = $container;
        $this->mailer = $mailer;
    }

    public function onLogoutSuccess(Request $request)
    {
        $user = $this->security->getToken()->getUser();

        if (in_array($user->getRoles()[0], ['ROLE_MANAGER'])) {
            $this->sendEmailAlert($user->getId());
        }

        return new RedirectResponse($this->router->generate('EA_login'));
    }

    public function sendEmailAlert($userId)
    {
        $em = $this->container->get('doctrine')->getManager();

        $adminEmails = $em->getRepository('ArcanysEasyAppBundle:User')
            ->getAdminEmails();

        $invoices = $em->getRepository('ArcanysEasyAppBundle:Invoice')
            ->findByPendingAlert();

        $mails = [];
        foreach($invoices as $key => $invoice) {
            $managerApproval = $invoice->getManagerApproval();

            // Send to admin of no manager assigned
            if (in_array($managerApproval, [99999, 0])) {
                $mails['admin'][] = $invoice->setSentAt(new \DateTime())->getInvoicenumber();
            }
            else if ($managerApproval == $userId) {
                $emailTo = $em->getRepository('ArcanysEasyAppBundle:User')
                    ->find($managerApproval)
                    ->getEmail();

                $mails[$emailTo][] = $invoice->setSentAt(new \DateTime())->getInvoicenumber();
            }

            $em->persist($invoice);
        }

        foreach($mails as $key => $value) {
            array_walk($value, function(&$item) {
                $item = 'Invoice No: ' . $item;
            });

            $this->mailer->sendInvoiceAlert(($key == 'admin' ? $adminEmails : $key), $value);
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            die(sprintf('<error>An Error occurred: %s</error>', $e->getMessage()));
        }
    }
}