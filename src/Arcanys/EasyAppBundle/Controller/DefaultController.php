<?php

namespace Arcanys\EasyAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ArcanysEasyAppBundle:Default:index.html.twig', array('name' => $name));
    }
}
