<?php

namespace Arcanys\EasyAppBundle\Controller;

use Arcanys\EasyAppBundle\Entity\Chartofaccounts;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ChartsController extends Controller
{
    public function insertAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $data       = $request->get('data');
            $acctnum    = $request->get('acctnum');
            $acctname   = $request->get('acctname');
            $user       = $this->get('security.context')->getToken()->getUser();

            $charts = new Chartofaccounts();
            $charts->setAddedby($user->getId());
            $charts->setChartname($data);
            $charts->setAccountnumber($acctnum);
            $charts->setAccountname($acctname);
            $charts->setCompany($user->getCompany());

            $em = $this->getDoctrine()->getManager();
            $em->persist($charts);
            $em->flush();

            $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
            $getChartsdata  = $repository->findOneBy( array('chartname' => $data, 'company' => $user->getCompany()) );

            $response = array( "success" => true, "id" => $getChartsdata->getId() );
        }
        return new JsonResponse($response);
    }

    public function modifyAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';
        $getData        = (isset($_POST['data'])) ? $_POST['data'] : '';
        $acctnum        = (isset($_POST['acctnum'])) ? $_POST['acctnum'] : '';
        $acctname       = (isset($_POST['acctname'])) ? $_POST['acctname'] : '';

        $repository     = $this->getDoctrine()->getManager();
        $getCharts      = $repository->getRepository('ArcanysEasyAppBundle:Chartofaccounts')
                                     ->find($getID);

        $getCharts->setChartname($getData);
        $getCharts->setAccountnumber($acctnum);
        $getCharts->setAccountname($acctname);
        $repository->flush();

        $response = array( "success" => true );
        return new JsonResponse($response);
    }

    public function deleteAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';

        $repository     = $this->getDoctrine()->getManager();
        $getCharts      = $repository->getRepository('ArcanysEasyAppBundle:Chartofaccounts')->find($getID);

        $repository->remove($getCharts);
        $repository->flush();

        $repo           = $this->getDoctrine()->getManager();
        $getAllCharts   = $repo->getRepository('ArcanysEasyAppBundle:Chartofaccounts')->findAll();

        if ( empty($getAllCharts) ) {
            $info = 1;
            $msg  = 'No charts of accounts available';
        } else {
            $info = 0;
            $msg  = '';
        }

        $response = array( "success" => true, 'msg' => $msg, 'info' => $info );
        return new JsonResponse($response);
    }

}