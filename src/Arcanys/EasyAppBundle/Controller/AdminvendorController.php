<?php

namespace Arcanys\EasyAppBundle\Controller;

use Arcanys\EasyAppBundle\Entity\Vendor;
use Arcanys\EasyAppBundle\Entity\Vendorcomments;
use Arcanys\EasyAppBundle\Entity\Invoiceebankinfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminvendorController extends Controller
{
    public function indexAction()
    {
        $request        = $this->get('request');
        $session        = $request->getSession();
        $company        = $session->get('company');
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $query          = $repository->createQueryBuilder('v')
                                     ->where('v.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('v.dateadded', 'DESC')
                                     ->getQuery();
        $vendor         = $query->getResult();

        // CHARTS OF ACCOUNTS
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getChartsdata  = $repository->findBy( array( 'company' => $company ) );

        // LIST OF STATE
        $state = array(
            'AL'=>'Alabama',
            'AK'=>'Alaska',
            'AZ'=>'Arizona',
            'AR'=>'Arkansas',
            'CA'=>'California',
            'CO'=>'Colorado',
            'CT'=>'Connecticut',
            'DE'=>'Delaware',
            'DC'=>'District of Columbia',
            'FL'=>'Florida',
            'GA'=>'Georgia',
            'HI'=>'Hawaii',
            'ID'=>'Idaho',
            'IL'=>'Illinois',
            'IN'=>'Indiana',
            'IA'=>'Iowa',
            'KS'=>'Kansas',
            'KY'=>'Kentucky',
            'LA'=>'Louisiana',
            'ME'=>'Maine',
            'MD'=>'Maryland',
            'MA'=>'Massachusetts',
            'MI'=>'Michigan',
            'MN'=>'Minnesota',
            'MS'=>'Mississippi',
            'MO'=>'Missouri',
            'MT'=>'Montana',
            'NE'=>'Nebraska',
            'NV'=>'Nevada',
            'NH'=>'New Hampshire',
            'NJ'=>'New Jersey',
            'NM'=>'New Mexico',
            'NY'=>'New York',
            'NC'=>'North Carolina',
            'ND'=>'North Dakota',
            'OH'=>'Ohio',
            'OK'=>'Oklahoma',
            'OR'=>'Oregon',
            'PA'=>'Pennsylvania',
            'RI'=>'Rhode Island',
            'SC'=>'South Carolina',
            'SD'=>'South Dakota',
            'TN'=>'Tennessee',
            'TX'=>'Texas',
            'UT'=>'Utah',
            'VT'=>'Vermont',
            'VA'=>'Virginia',
            'WA'=>'Washington',
            'WV'=>'West Virginia',
            'WI'=>'Wisconsin',
            'WY'=>'Wyoming',
        );

        return $this->render('ArcanysEasyAppBundle:AdminvendorDashboard:index.html.twig', array(
            'vendor' => $vendor,
            'charts' => $getChartsdata,
            'state'  => $state
        ));
    }

    public function createAction(Request $request)
    {
        if ( $request->getMethod() == 'POST' ) {
            $name           = $request->get('name');
            $address        = $request->get('address');
            $city           = $request->get('city');
            $state          = $request->get('state');
            $zip            = $request->get('zip');
            $contact        = $request->get('contact');
            $acctnumber     = $request->get('acctnumber');
            $phone          = $request->get('phone');
            $local          = $request->get('local');
            $form1099       = $request->get('1099');
            $W9             = $request->get('W9');
            $paymentterms   = $request->get('paymentterms');
            $email          = $request->get('email');
            $charts         = $request->get('chartofaccounts');
            $comments       = $request->get('comments');
            $user           = $this->get('security.context')->getToken()->getUser();

            $vendor = new Vendor();
            $vendor->setName($name);
            $vendor->setAddress($address);
            $vendor->setCity($city);
            $vendor->setState($state);
            $vendor->setZip($zip);
            $vendor->setContactPerson($contact);
            $vendor->setPhoneNum($phone);
            $vendor->setLocalNum($local);
            $vendor->setAcctnumber($acctnumber);
            $vendor->setTaxform($form1099);
            $vendor->setW9form($W9);
            $vendor->setPaymentterm($paymentterms);
            $vendor->setEmail($email);
            $vendor->setChartsofaccounts($charts);
            $vendor->setStatus('1');
            $vendor->setCompany($user->getCompany());
            //$getEntity      = $repository->findAll();

            $em = $this->getDoctrine()->getManager();
            $em->persist($vendor);
            $em->flush();

            $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
            $getVendor      = $repository->findOneBy(
                array('name' => $name, 'email' => $email)
            );

            $message = new Vendorcomments();
            $message->setVendorId($getVendor->getId());
            $message->setStatus('1');
            $message->setComments($comments);

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

            $response       = array( "code" => 100, "success" => true, "id" => $getVendor->getId() );
            return new Response( json_encode($response) );
        }
    }

    public function viewajaxAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';
        $request        = $this->get('request');
        $session        = $request->getSession();
        $company        = $session->get('company');
        // GET LIST OF VENDORS
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $vendor         = $repository->find($getID);

        $repository2    = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendorcomments');
        $comment        = $repository2->findBy( array('vendorId' => $getID) );

        // CHARTS OF ACCOUNTS
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getChartsdata  = $repository->findBy( array( 'company' => $company ) );
        $repocharts     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $vendorCharts   = $repocharts->findBy( array( 'id' => $vendor->getChartsofaccounts() ) );

        // LIST OF STATE
        $state = array(
            'AL'=>'Alabama',
            'AK'=>'Alaska',
            'AZ'=>'Arizona',
            'AR'=>'Arkansas',
            'CA'=>'California',
            'CO'=>'Colorado',
            'CT'=>'Connecticut',
            'DE'=>'Delaware',
            'DC'=>'District of Columbia',
            'FL'=>'Florida',
            'GA'=>'Georgia',
            'HI'=>'Hawaii',
            'ID'=>'Idaho',
            'IL'=>'Illinois',
            'IN'=>'Indiana',
            'IA'=>'Iowa',
            'KS'=>'Kansas',
            'KY'=>'Kentucky',
            'LA'=>'Louisiana',
            'ME'=>'Maine',
            'MD'=>'Maryland',
            'MA'=>'Massachusetts',
            'MI'=>'Michigan',
            'MN'=>'Minnesota',
            'MS'=>'Mississippi',
            'MO'=>'Missouri',
            'MT'=>'Montana',
            'NE'=>'Nebraska',
            'NV'=>'Nevada',
            'NH'=>'New Hampshire',
            'NJ'=>'New Jersey',
            'NM'=>'New Mexico',
            'NY'=>'New York',
            'NC'=>'North Carolina',
            'ND'=>'North Dakota',
            'OH'=>'Ohio',
            'OK'=>'Oklahoma',
            'OR'=>'Oregon',
            'PA'=>'Pennsylvania',
            'RI'=>'Rhode Island',
            'SC'=>'South Carolina',
            'SD'=>'South Dakota',
            'TN'=>'Tennessee',
            'TX'=>'Texas',
            'UT'=>'Utah',
            'VT'=>'Vermont',
            'VA'=>'Virginia',
            'WA'=>'Washington',
            'WV'=>'West Virginia',
            'WI'=>'Wisconsin',
            'WY'=>'Wyoming',
        );

        return $this->render('ArcanysEasyAppBundle:AdminvendorDashboard:displayVendor.html.twig',array(
            'vendor'   => $vendor,
            'comment'  => $comment,
            'state'    => $state,
            'charts'   => $getChartsdata,
            'getchart' => $vendorCharts
        ));
    }

    public function viewAction($id)
    {
        $request        = $this->get('request');
        $session        = $request->getSession();
        $company        = $session->get('company');
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $query          = $repository->createQueryBuilder('v')
                                     ->where('v.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('v.dateadded', 'DESC')
                                     ->getQuery();
        $vVendor        = $query->getResult();
        $vendor         = $repository->find($id);

        $repository2    = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendorcomments');
        $vendorc        = $repository2->findByVendorId($id);

        // CHARTS OF ACCOUNTS
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getChartsdata  = $repository->findBy( array( 'company' => $company ) );
        $repocharts     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $vendorCharts   = $repocharts->findBy( array( 'id' => $vendor->getChartsofaccounts() ) );

        // LIST OF STATE
        $state = array(
            'AL'=>'Alabama',
            'AK'=>'Alaska',
            'AZ'=>'Arizona',
            'AR'=>'Arkansas',
            'CA'=>'California',
            'CO'=>'Colorado',
            'CT'=>'Connecticut',
            'DE'=>'Delaware',
            'DC'=>'District of Columbia',
            'FL'=>'Florida',
            'GA'=>'Georgia',
            'HI'=>'Hawaii',
            'ID'=>'Idaho',
            'IL'=>'Illinois',
            'IN'=>'Indiana',
            'IA'=>'Iowa',
            'KS'=>'Kansas',
            'KY'=>'Kentucky',
            'LA'=>'Louisiana',
            'ME'=>'Maine',
            'MD'=>'Maryland',
            'MA'=>'Massachusetts',
            'MI'=>'Michigan',
            'MN'=>'Minnesota',
            'MS'=>'Mississippi',
            'MO'=>'Missouri',
            'MT'=>'Montana',
            'NE'=>'Nebraska',
            'NV'=>'Nevada',
            'NH'=>'New Hampshire',
            'NJ'=>'New Jersey',
            'NM'=>'New Mexico',
            'NY'=>'New York',
            'NC'=>'North Carolina',
            'ND'=>'North Dakota',
            'OH'=>'Ohio',
            'OK'=>'Oklahoma',
            'OR'=>'Oregon',
            'PA'=>'Pennsylvania',
            'RI'=>'Rhode Island',
            'SC'=>'South Carolina',
            'SD'=>'South Dakota',
            'TN'=>'Tennessee',
            'TX'=>'Texas',
            'UT'=>'Utah',
            'VT'=>'Vermont',
            'VA'=>'Virginia',
            'WA'=>'Washington',
            'WV'=>'West Virginia',
            'WI'=>'Wisconsin',
            'WY'=>'Wyoming',
        );

        return $this->render('ArcanysEasyAppBundle:AdminvendorDashboard:viewVendor.html.twig',array(
            'vendor'   => $vendor,
            'comment'  => $vendorc,
            'state'    => $state,
            'charts'   => $getChartsdata,
            'getchart' => $vendorCharts,
            'vvendor'  => $vVendor
        ));
    }

    public function viewdetailAction($id)
    {
        $request        = $this->get('request');
        $session        = $request->getSession();
        $company        = $session->get('company');
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT
                                i.invoiceId, i.description, i.invoicenumber, i.status, i.dueDate, i.amount, i.checkNo, i.dateadded, i.dateupdated, i.idEntity,
                                e.bankAcct, e.bankName, e.entityName, e.id,
                                eb.bankName, eb.entityNumId, eb.bankAcct,
                                ib.id, ib.invoiceinfoId, ib.entitybankinfoId, ib.entityId,
                                v.id, v.name
                            FROM ArcanysEasyAppBundle:Invoice i
                            JOIN ArcanysEasyAppBundle:Vendor v
                                WITH v.id = i.idVendor
                            LEFT JOIN ArcanysEasyAppBundle:Entity e
                                WITH e.id = i.idEntity
                            JOIN ArcanysEasyAppBundle:Invoiceebankinfo ib
                                WITH ib.entityId = i.idEntity
                            JOIN ArcanysEasyAppBundle:Entitybankinfo eb
                                WITH eb.entityNumId = ib.entityId
                            WHERE
                                i.status IN (4, 10, 33, 55) AND
                                i.deletestatus = 1 AND
                                v.id = '" . $id . "' AND
                                v.company = '" . $company[0] . "'
                            GROUP BY i.invoiceId
                            ORDER BY i.dateupdated DESC
                            "
                        );
        $vVendor        = $query->getResult();

        $repository2    = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendorcomments');
        $vendorc        = $repository2->findByVendorId($id);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $vVendor, $this->get('request')->query->get('page', 1), 5
        );

        $vendor         = $repository->find($id);

        return $this->render('ArcanysEasyAppBundle:AdminvendorDashboard:viewVendorDetail.html.twig',array(
            'vendor'   => $vendor,
            'comment'  => $vendorc,
            'vvendor'  => $pagination
        ));
    }

    public function registryAction()
    {
        $request        = $this->get('request');
        $session        = $request->getSession();
        $company        = $session->get('company');
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $query          = $repository->createQueryBuilder('v')
                                     ->where('v.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('v.name', 'ASC')
                                     ->getQuery();
        $vendor         = $query->getResult();

        $letters        = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
                                'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');

        return $this->render('ArcanysEasyAppBundle:AdminvendorDashboard:registry.html.twig', array(
            'letters' => $letters,
            'vendor'  => $vendor
        ));
    }

    public function registryviewAction($page)
    {
        $request        = $this->get('request');
        $session        = $request->getSession();
        $company        = $session->get('company');
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $vendor         = $repository->createQueryBuilder('v')
                                     ->where('v.name LIKE :name')
                                     ->andWhere('v.company = :company')
                                     ->setParameter('name', '' . $page . '%')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('v.name', 'ASC')
                                     ->getQuery()->getResult();

        $letters        = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
            'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');

        return $this->render('ArcanysEasyAppBundle:AdminvendorDashboard:registryview.html.twig', array(
            'letters' => $letters,
            'vendor'  => $vendor
        ));
    }

    public function searchAction(Request $request)
    {
        $getData        = $request->query->get('searchkey');
        $session        = $request->getSession();
        $company        = $session->get('company');
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $vendor         = $repository->createQueryBuilder('v')
                                     ->where('v.name LIKE :name')
                                     ->andWhere('v.company = :company')
                                     ->setParameter('name', '%' . $getData . '%')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('v.name', 'ASC')
                                     ->getQuery()->getResult();

        $letters        = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
            'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');

        return $this->render('ArcanysEasyAppBundle:AdminvendorDashboard:search.html.twig', array(
            'letters' => $letters,
            'vendor'  => $vendor
        ));
    }

    public function deleteAction()
    {
        $getID       = (isset($_POST['id'])) ? $_POST['id'] : '';

        $em          = $this->getDoctrine()->getManager();
        $vendor      = $em->getRepository('ArcanysEasyAppBundle:Vendor')->find($getID);

        $em2         = $this->getDoctrine()->getManager();
        $comment     = $em2->getRepository('ArcanysEasyAppBundle:Vendorcomments')->findOneBy( array('vendorId' => $getID) );

        $em->remove($vendor);
        $em->flush();

        if ($comment =! NULL) {
            echo 'NULL';
        } else {
            $em2->remove($comment);
            $em2->flush();
            echo 'NOT NULL';
        }

        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $query          = $repository->createQueryBuilder('e')
                                     ->orderBy('e.id', 'DESC')
                                     ->setMaxResults(1)
                                     ->getQuery();
        $getvendor      = $query->getResult();

        //var_dump($getvendor); echo $getvendor[0]->getId();
        $getVendorID = '';
        if ($getvendor =! NULL) {
            $getVendorID = '';
        } else {
            $getVendorID = $getvendor[0]->getId();
        }

        $response       = array( "code" => 100, "success" => true, "id" => $getVendorID );
        return new Response( json_encode($response) );
    }

    public function updateAction($id, Request $request)
    {
        $em         = $this->getDoctrine()->getManager();
        $vendor     = $em->getRepository('ArcanysEasyAppBundle:Vendor')->find($id);
        $comment    = $em->getRepository('ArcanysEasyAppBundle:Vendorcomments')
                         ->findOneBy( array('vendorId' => $id) );

        /*var_dump($user);
        exit();*/
        if ( $request->getMethod() == 'POST' ) {
            $name         = $request->get('name');
            $address      = $request->get('address');
            $city         = $request->get('city');
            $state        = $request->get('state');
            $zip          = $request->get('zip');
            $contact      = $request->get('contact');
            $acctnumber   = $request->get('acctnumber');
            $phone        = $request->get('phone');
            $local        = $request->get('local');
            $form1099     = $request->get('1099');
            $W9           = $request->get('W9');
            $paymentterms = $request->get('paymentterms');
            $email        = $request->get('email');
            $charts       = $request->get('chartofaccounts');
            $comments     = $request->get('comments');

            $vendor->setName($name);
            $vendor->setAddress($address);
            $vendor->setCity($city);
            $vendor->setState($state);
            $vendor->setZip($zip);
            $vendor->setContactPerson($contact);
            $vendor->setAcctnumber($acctnumber);
            $vendor->setPhoneNum($phone);
            $vendor->setLocalNum($local);
            $vendor->setTaxform($form1099);
            $vendor->setW9form($W9);
            $vendor->setPaymentterm($paymentterms);
            $vendor->setChartsofaccounts($charts);
            $vendor->setEmail($email);

            $comment->setComments($comments);
            $em->flush();

            $getcharts = $em->getRepository('ArcanysEasyAppBundle:Chartofaccounts')->find($charts);

            $response = array( "code" => 100, "success" => true, "charts" => $getcharts->getChartname() );
            return new JsonResponse($response);
        }
    }

    public function getyearexpenseAction($id)
    {
        $getFirstMonth  = date("Y-01-01 09:46:29");
        $getCurrentdate = date("Y-m-d H:i:s");

        $em             = $this->getDoctrine()->getManager();
        // $query          = $em->createQuery(
                            // "SELECT i.invoiceId, i.dueDate, i.status, i.amount, i.dateadded, e.entityName, e.id, v.id, v.name
                            // FROM ArcanysEasyAppBundle:Entity e
                            // JOIN ArcanysEasyAppBundle:Invoice i WITH i.idEntity = e.id
                            // JOIN ArcanysEasyAppBundle:Vendor v WITH i.idVendor = v.id
                            // WHERE i.dateadded > '$getFirstMonth' AND i.dateadded < '$getCurrentdate'
                            // AND i.status = '0' OR i.status = '4' OR i.status = '3' OR i.status = '33'
                            // AND i.idVendor = " . $id . "
                            // GROUP BY i.invoiceId"
                        // );
		$query			= $em->createQuery(
							"
							SELECT 
							  SUM(i.amount)
							FROM
							  ArcanysEasyAppBundle:Invoice i
							WHERE i.idVendor = " . $id . "
							AND i.dateadded > '$getFirstMonth' AND i.dateadded < '$getCurrentdate'
							AND i.status IN (4, 10, 33, 55) AND
                                i.deletestatus = 1
							"
						);
        $vVendor        = $query->getResult();
		$total = (float) $vVendor[0][1];

        // $total = 0;
        // foreach ($vVendor as $value) {
            // $total += $value['amount'];
        // }

        $response = array( "code" => 100, "success" => true, "total" => $total );
        return new JsonResponse($response);
    }

    public function getdatetodateexpenseAction($id)
    {
        $getFromMonth   = (isset($_POST['from'])) ? $_POST['from'] : '';
        $getToMonth     = (isset($_POST['to'])) ? $_POST['to'] : '';

        $FromMonth      = date('Y-m-d 00:00:00', strtotime($getFromMonth));
        $ToMonth        = date('Y-m-d 23:59:59', strtotime($getToMonth));

        $em             = $this->getDoctrine()->getManager();
        // $query          = $em->createQuery(
                            // "SELECT
                                // i.invoiceId, i.dueDate, i.status, i.amount, i.dateadded,
                                // e.entityName, e.id,
                                // v.id, v.name
                            // FROM ArcanysEasyAppBundle:Invoice i
                            // JOIN ArcanysEasyAppBundle:Vendor v
                                // WITH v.id = i.idVendor
                            // JOIN ArcanysEasyAppBundle:Entity e
                                // WITH e.id = i.idEntity
                            // WHERE
                                // v.id = '" . $id . "'
                            // AND
                                // i.dateadded >= '$FromMonth' AND i.dateadded <= '$ToMonth'
                            // GROUP BY i.invoiceId"
                        // );
        // $vVendor        = $query->getResult();
		
		$query			= $em->createQuery(
							"
							SELECT 
							  SUM(i.amount)
							FROM
							  ArcanysEasyAppBundle:Invoice i
							WHERE i.idVendor = " . $id . "
							AND i.dateadded >= '$FromMonth' AND i.dateadded <= '$ToMonth'
							AND i.status IN (4, 10, 33, 55) AND
                                i.deletestatus = 1
							"
						);
        $vVendor        = $query->getResult();
		$total = (float) $vVendor[0][1];

        // $total = 0;
        // foreach ($vVendor as $value) {
            // $total += $value['amount'];
        // }

        $response = array( "code" => 100, "success" => true, "total" => $total );
        return new JsonResponse($response);
    }

    public function getmonthexpenseAction($id)
    {
        $getFirstMonth  = date("Y-m-01 00:00:00");
        $getEndMonth    = date("Y-m-t H:i:s");

        $em             = $this->getDoctrine()->getManager();
        // $query          = $em->createQuery(
                            // "SELECT
                                // i.invoiceId, i.dueDate, i.amount, i.status, i.dateadded,
                                // e.entityName, e.id,
                                // v.id, v.name
                            // FROM ArcanysEasyAppBundle:Entity e
                            // JOIN ArcanysEasyAppBundle:Invoice i
                                // WITH i.idEntity = e.id
                            // JOIN ArcanysEasyAppBundle:Vendor v
                                // WITH i.idVendor = v.id
                            // WHERE
                                // i.dateadded > '$getFirstMonth' AND i.dateadded < '$getEndMonth'
                            // AND
                                // v.id = " . $id . "
                            // GROUP BY i.invoiceId"
        // );
        // $vVendor        = $query->getResult();
		
		$query			= $em->createQuery(
							"
							SELECT 
							  SUM(i.amount)
							FROM
							  ArcanysEasyAppBundle:Invoice i
							WHERE i.idVendor = " . $id . "
							AND i.dateadded > '$getFirstMonth' AND i.dateadded < '$getEndMonth'
							AND i.status IN (4, 10, 33, 55) AND
                                i.deletestatus = 1
							"
						);
        $vVendor        = $query->getResult();
		$total = (float) $vVendor[0][1];

        // $total = 0;
        // foreach ($vVendor as $value) {
            // $total += $value['amount'];
        // }

        $response = array( "code" => 100, "success" => true, "total" => $total );
        return new JsonResponse($response);
    }

    public function checkemailAction()
    {
        $getEmail       = (isset($_POST['keyword'])) ? $_POST['keyword'] : '';

        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $email          = $repository->createQueryBuilder('u')
            ->where('u.email LIKE :email')
            ->setParameter('email', '%' . $getEmail . '%')
            ->getQuery();
        $checkemail     = $email->getResult();

        if (!filter_var($getEmail, FILTER_VALIDATE_EMAIL)) {
            $emailresponse  = 'Email is not valid!';
            $info = 1;
        } else if (empty($checkemail)) {
            $emailresponse  = '';
            $info = 0;
        } else {
            $emailresponse  = 'Email already exist!';
            $info = 2;
        }

        $response       = array( "code" => 100, "success" => true, 'email' => $emailresponse, 'info' => $info );
        return new JsonResponse($response);
    }

    public function downloadcsvAction($id, Request $request)
    {
        $val = $request->request->all();

        //print_r($val['header']); exit();

        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT
                                i.invoiceId, i.description, i.invoicenumber, i.status, i.dueDate, i.amount, i.checkNo, i.dateadded, i.dateupdated, i.idEntity,
                                e.bankAcct, e.bankName, e.entityName, e.id,
                                eb.bankName, eb.entityNumId, eb.bankAcct,
                                ib.id, ib.invoiceinfoId, ib.entitybankinfoId, ib.entityId,
                                v.id, v.name
                            FROM ArcanysEasyAppBundle:Invoice i
                            JOIN ArcanysEasyAppBundle:Vendor v
                                WITH v.id = i.idVendor
                            LEFT JOIN ArcanysEasyAppBundle:Entity e
                                WITH e.id = i.idEntity
                            JOIN ArcanysEasyAppBundle:Invoiceebankinfo ib
                                WITH ib.entityId = i.idEntity
                            JOIN ArcanysEasyAppBundle:Entitybankinfo eb
                                WITH eb.entityNumId = ib.entityId
                            WHERE
                                i.status IN (4, 10, 33, 55) AND
                                i.deletestatus = 1 AND
                                v.id = '" . $id . "'
                            GROUP BY i.invoiceId
                            ORDER BY i.dateupdated DESC"
        );
        $vendor        = $query->getArrayResult();

        usort($vendor, function ($a, $b) {
            if($a['dateadded'] == $b['dateadded']){
                return 0;
            }
            return ($a['dateadded'] > $b['dateadded']) ? -1 : 1;
        });

        $filename = "export_".date("Y_m_d_His").".csv";

        $response = $this->render('ArcanysEasyAppBundle:AdminvendorDashboard:csv.html.twig', array('data' => $vendor, 'val' => $val['header']));

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Description', 'Submissions Export');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

}
