<?php

namespace Arcanys\EasyAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecretaryController extends Controller
{
    public function indexAction()
    {
        return $this->render('ArcanysEasyAppBundle:Dashboard:index.html.twig');
    }
}
