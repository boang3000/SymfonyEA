<?php

namespace Arcanys\EasyAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LoginController extends Controller
{
    public function indexAction()
    {
        return $this->render('ArcanysEasyAppBundle:Login:index.html.twig');
    }

    public function checkAction()
    {
        return $this->render('ArcanysEasyAppBundle:Login:index.html.twig');
    }
}
