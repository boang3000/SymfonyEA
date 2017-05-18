<?php

namespace Arcanys\EasyAppBundle\Controller;

use Arcanys\EasyAppBundle\Entity\Entity;
use Arcanys\EasyAppBundle\Entity\Entitybankinfo;
use Arcanys\EasyAppBundle\Entity\Entitycomments;
use Arcanys\EasyAppBundle\Entity\Entityimages;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminentityController extends Controller
{
    public function indexAction()
    {
        $today              = date('mdYHi');
        $startDate          = date('mdYHi', strtotime('03-14-2012 09:06:00'));
        $range              = $startDate - $today;
        $rand               = rand(0, $range);
        $generatecheck      = md5($rand . ($startDate + $rand));
        $request            = $this->get('request');
        $user               = $this->get('security.context')->getToken()->getUser();
        $session            = $request->getSession();
        $company            = $session->get('company');

        $repository         = $this->getDoctrine()->getManager();
        $entity             = $repository->
                                    createQuery(
                                        "SELECT e
                                         FROM ArcanysEasyAppBundle:Entity e
                                         WHERE e.company = '" . $company[0] . "'
                                         ORDER BY e.dateadded DESC"
                                    )->getArrayResult();
		$entity = $this->get('entity.values.handler')->getCurrentBalance($entity);

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

		return $this->render('ArcanysEasyAppBundle:AdminentityDashboard:index.html.twig', array(
            'entity'     => $entity,
            'state'      => $state,
            'checknum1'  => $generatecheck . uniqid(rand(0, $range)),
            'checknum2'  => uniqid(rand(0, $range)) . $generatecheck,
            'checknum3'  => $generatecheck . uniqid(rand(0, $range)),
            'checknum4'  => uniqid(rand(0, $range)) . $generatecheck,
            'checknum5'  => $generatecheck . uniqid(rand(0, $range))
        ));
    }

    public function createAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        if ( $request->getMethod() == 'POST' ) {
            $entityname     = $request->get('entityname');
            $bankname       = $request->get('bankname');
            $bankcontact    = $request->get('bankcontact');
            $bankphonenum   = $request->get('bankphonenum');
            $bankemail      = $request->get('bankemail');
            $bankaddress    = $request->get('bankaddress');
            $city           = $request->get('city');
            $state          = $request->get('state');
            $zipcode        = $request->get('zipcode');
            $bankaccount    = $request->get('bankaccount');
            $bankroute      = $request->get('bankroute');
            $token          = $request->get('token');
            $startbalance   = str_replace(',', '', $request->get('startbalance'));
            $curbalance     = str_replace(',', '', $request->get('curbalance'));
            $comments       = $request->get('comments');

            $entity = new Entity();
            $entity->setEntityName($entityname);
            $entity->setCompany($user->getCompany());

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
            $getEntity      = $repository->findOneBy( array('entityName' => $entityname) );

            for ( $i = 0; $i < count($bankname); $i++ ) {
                $banknameabbr   = preg_replace('~\b(\w)|.~', '$1', $bankname[$i]);
                $bankacctdigits = substr($bankaccount[$i], -4);
                $entitytabname  = strtoupper($banknameabbr) . $bankacctdigits;
                $entitybankinfo = new Entitybankinfo();

                if ( empty($bankname[$i]) || empty($bankaccount[$i]) ||
                    empty($bankcontact[$i]) || empty($bankroute[$i]) ||
                    empty($startbalance[$i]) || empty($curbalance[$i]) )
                    continue;

                $entitybankinfo->setEntityNumId($getEntity->getId());
                $entitybankinfo->setBankName($bankname[$i]);
                $entitybankinfo->setBankAcct($bankaccount[$i]);
                $entitybankinfo->setBankContact($bankcontact[$i]);
                $entitybankinfo->setEntityContact($bankphonenum[$i]);
                $entitybankinfo->setEntityEmailaddress($bankemail[$i]);
                $entitybankinfo->setEntityAddress($bankaddress[$i]);
                $entitybankinfo->setEntityCity($city[$i]);
                $entitybankinfo->setEntityState($state[$i]);
                $entitybankinfo->setEntityZip($zipcode[$i]);
                $entitybankinfo->setRoutingBalance($bankroute[$i]);
                $entitybankinfo->setStartBalance($startbalance[$i]);
                $entitybankinfo->setCurBalance($curbalance[$i]);
                $entitybankinfo->setEntityTabname($entitytabname);
                $entitybankinfo->setToken($token[$i]);
                $entitybankinfo->setComments($comments[$i]);

                $em->persist($entitybankinfo);
            }
            $em->flush();

            return new JsonResponse(['code' => 100, 'success' => true, 'id' => $getEntity->getId()]);
            //return $this->redirect( $this->generateUrl( 'EA_admin_displayentity', array( 'id' => $getEntity->getId() ) ) );
        }
    }

    public function updateAction($id, Request $request)
    {
        if ($request->getMethod() != 'POST') {
            return new JsonResponse(['code' => 100, 'success' => false]);
        }

        $input = json_decode($request->get('input'));
        $textName = $request->get('textName');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ArcanysEasyAppBundle:Entity')->find($id);
        $entity->setEntityName($textName);
        $em->persist($entity);

        foreach($input as $key => $value) {
            if (!isset($value->entitybankId)) {
                continue;
            }

            if (!$value->entitybankId) {
                if ($value->bankname) {
                    $banknameabbr   = preg_replace('~\b(\w)|.~', '$1', $value->bankname);
                    $bankacctdigits = substr($value->bankaccount, -4);
                    $entitytabname  = strtoupper($banknameabbr) . $bankacctdigits;

                    $entityBankInfo = new Entitybankinfo();
                    $entityBankInfo->setEntityNumId($id);
                    $entityBankInfo->setBankName($value->bankname);
                    $entityBankInfo->setBankAcct($value->bankaccount);
                    $entityBankInfo->setBankContact($value->bankcontact);
                    $entityBankInfo->setEntityContact($value->bankphonenum);
                    $entityBankInfo->setEntityEmailaddress($value->bankemail);
                    $entityBankInfo->setEntityAddress($value->bankaddress);
                    $entityBankInfo->setEntityCity($value->city);
                    $entityBankInfo->setEntityState($value->state);
                    $entityBankInfo->setEntityZip($value->zipcode);
                    $entityBankInfo->setRoutingBalance($value->bankroute);
                    $entityBankInfo->setStartBalance($value->startbalance);
                    $entityBankInfo->setCurBalance($value->curbalance);
                    $entityBankInfo->setEntityTabname($entitytabname);
                    $entityBankInfo->setComments($value->comments);

                    $em->persist($entityBankInfo);
                }
                continue;
            }

            $bankInfo = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')->find($value->entitybankId);
            $bankInfo->setBankName($value->bankname);
            $bankInfo->setBankAcct($value->bankaccount);
            $bankInfo->setBankContact($value->bankcontact);
            $bankInfo->setEntityContact($value->bankphonenum);
            $bankInfo->setEntityEmailaddress($value->bankemail);
            $bankInfo->setEntityAddress($value->bankaddress);
            $bankInfo->setEntityCity($value->city);
            $bankInfo->setEntityState($value->state);
            $bankInfo->setEntityZip($value->zipcode);
            $bankInfo->setRoutingBalance($value->bankroute);
            $bankInfo->setStartBalance($value->startbalance);
            $bankInfo->setCurBalance($value->curbalance);
            $bankInfo->setComments($value->comments);

            $em->persist($bankInfo);
        }

        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['code' => 100, 'success' => false, 'msg' => $e->getMessage()]);
        }

        return new JsonResponse(['code' => 100, 'success' => true]);
    }

    public function uploadAction()
    {
        if (empty($_FILES)) {
            return new JsonResponse(["code" => 100, "success" => false]);
        }

        $file = $_FILES['file'];
        $token = $this->get('request')->get('token');
        $entityId = $this->get('request')->get('entityId', 0);
        $bankno = $this->get('request')->get('bankno');

        // Validation
        $pathInfo = pathinfo($file['name']);
        if (!in_array($pathInfo['extension'], ['jpg','jpeg','gif','png','pdf'])) {
            return new JsonResponse(["code" => 100, "success" => false, 'msg' => 'Invalid file type.']);
        }

        $today = date("mdYGis");
        $targetFolder = $this->container->getParameter('target_folder');
        $tempFile   = $file['tmp_name'];
        $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
        $targetFile = rtrim($targetPath,'/') . '/' . md5(date("mdYGis")) . $file['name'];

        try {
            @move_uploaded_file($tempFile, $targetFile);

            $image = new Entityimages();
            $image->setEntityId($entityId);
            $image->setUpltoken($token);
            $image->setFileName($fileName = md5($today) . $file['name']);
            $image->setStatus('1');
            $image->setBankno($bankno);

            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['code' => 100, 'success' => false, 'msg' => $e->getMessage()]);
        }

        return new JsonResponse([
                'code' => 100,
                'success' => true,
                'data' => [
                    'id' => $image->getId(),
                    'fileName' => $fileName
                ]
            ]
        );
    }

    public function updateuploadAction()
    {
        if (empty($_FILES)) {
            return new JsonResponse(["code" => 100, "success" => false]);
        }

        $file = $_FILES['file'];
        $entityId = $this->get('request')->get('id');
        $token = $this->get('request')->get('token');

        // Validation
        $pathInfo = pathinfo($file['name']);
        if (!in_array($pathInfo['extension'], ['jpg','jpeg','gif','png'])) {
            return new JsonResponse(["code" => 100, "success" => false, 'msg' => 'Invalid file type.']);
        }

        $today = date("mdYGis");
        $targetFolder = $this->container->getParameter('target_folder');
        $tempFile   = $_FILES['Filedata']['tmp_name'];
        $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
        $targetFile = rtrim($targetPath, '/') . '/' . md5(date("mdYGis")) . $_FILES['Filedata']['name'];

        try {
            @move_uploaded_file($file['tmp_name'], $targetFile);

            $image = new Entityimages();
            $image->setEntityId($entityId);
            $image->setUpltoken($token);
            $image->setFileName($fileName = md5($today) . $file['name']);
            $image->setStatus('1');

            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['code' => 100, 'success' => false, 'msg' => $e->getMessage()]);
        }

        return new JsonResponse([
                'code' => 100,
                'success' => true,
                'data' => [
                    'id' => $image->getId(),
                    'fileName' => $fileName
                ]
            ]
        );
    }

    public function retrieveentityimgAction()
    {
        $request            = $this->get('request');
        $token              = $request->get('token');

        $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entityimages');
        $getEntityimg       = $repository->findBy( array('upltoken' => $token) );

        $str = array();
        foreach ($getEntityimg as $key => $value) {
			end($getEntityimg);
            if ($key === key($getEntityimg))
                $str[]  = $value->getFileName();
        }

        $response           = array( "success" => true, "name" => $str );
        return new JsonResponse($response);
    }

    public function viewajaxAction()
    {
        $getID              = (isset($_POST['id'])) ? $_POST['id'] : '';
        $today              = date('mdYHi');
        $startDate          = date('mdYHi', strtotime('03-14-2012 09:06:00'));
        $range              = $startDate - $today;
        $rand               = rand(0, $range);
        $generatecheck      = md5($rand . ($startDate + $rand));

        $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $entity             = $repository->find($getID);

        $repoentitybank     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entitybankinfo');
        $entitybankinfo     = $repoentitybank->findBy( array('entityNumId' => $entity->getId()) );

        if ( strlen($entity->getToken()) == 0 ) {
            $token = '';
        } else {
            $token = $entity->getToken();
        }

        $repository3        = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entityimages');
        $entityimg          = $repository3->findBy( array('upltoken' => $token) );

        $entityId = $getID;
        $bankno = 1;

        $em = $this->getDoctrine()->getManager();
        $entityImages = $em->getRepository('ArcanysEasyAppBundle:Entityimages')
            ->findBy(['entityId' => $entityId, 'bankno' => $bankno]);

        $images = [];
        foreach($entityImages as $key => $image) {
            $images[] = [
                'id' => $image->getId(),
                'fileName' => $image->getFilename()
            ];
        }

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

        return $this->render('ArcanysEasyAppBundle:AdminentityDashboard:displayEntity.html.twig',array(
            'inimg'    => $images,
            'state'    => $state,
            'entity'   => $entity,
            'entimg'   => $entityimg,
            'bankinfo' => $entitybankinfo,
            'checknum' => $generatecheck
        ));
    }

    public function viewAction($id)
    {
        $today              = date('mdYHi');
        $startDate          = date('mdYHi', strtotime('03-14-2012 09:06:00'));
        $range              = $startDate - $today;
        $rand               = rand(0, $range);
        $generatecheck      = md5($rand . ($startDate + $rand));
        $request            = $this->get('request');
        $user               = $this->get('security.context')->getToken()->getUser();
        $session            = $request->getSession();
        $company            = $session->get('company');

        $repository         = $this->getDoctrine()->getManager();
        $ventity            = $repository->
                                createQuery(
                                    "SELECT e
                                     FROM ArcanysEasyAppBundle:Entity e
                                     WHERE e.company = '" . $user->getCompany() . "'
                                     ORDER BY e.dateadded DESC"
                                )->getArrayResult();

        $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $entity             = $repository->find($id);

        $repoentitybank     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entitybankinfo');
        $entitybankinfo     = $repoentitybank->findBy( array('entityNumId' => $entity->getId()) );
        $repository3        = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entityimages');

        $entityId = $id;
        $bankno = 1;

        $em = $this->getDoctrine()->getManager();
        $entityImages = $em->getRepository('ArcanysEasyAppBundle:Entityimages')
                           ->findBy(['entityId' => $entityId, 'bankno' => $bankno]);

        $images = [];
        foreach($entityImages as $key => $image) {
            $images[] = [
                'id' => $image->getId(),
                'fileName' => $image->getFilename()
            ];
        }

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

        $entityb   = array();
        $entityimg = array();
        foreach ( $entitybankinfo as $key => $bankinfo ) {
            $entityb[]   = $bankinfo->getToken();
            $entityimg[] = $repository3->findBy( array('upltoken' => $entityb[$key]) );
        }

        return $this->render('ArcanysEasyAppBundle:AdminentityDashboard:viewEntity.html.twig', array(
            'entity'     => $entity,
            'entimg'     => $entityimg,
            'inimg'      => $images,
            'state'      => $state,
            'ventity'    => $ventity,
            'bankinfo'   => $entitybankinfo,
            'checknum'   => $generatecheck,
            'checknum1'  => $generatecheck . uniqid(rand(0, $range)),
            'checknum2'  => uniqid(rand(0, $range)) . $generatecheck,
            'checknum3'  => $generatecheck . uniqid(rand(0, $range)),
            'checknum4'  => uniqid(rand(0, $range)) . $generatecheck,
            'checknum5'  => $generatecheck . uniqid(rand(0, $range))
        ));
    }

    public function viewdetailAction($id)
    {
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');

        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT
                                i.invoiceId, i.description, i.checkNo, i.invoicenumber, i.dueDate, i.amount, dateformat(i.dateadded, '%m-%d-%Y') as dateadded, i.dateupdated, i.status,
                                e.entityName, e.id, e.token,
                                v.id, v.name
                            FROM ArcanysEasyAppBundle:Entity e
                            JOIN ArcanysEasyAppBundle:Invoice i
                                WITH i.idEntity = e.id
                            LEFT JOIN ArcanysEasyAppBundle:Vendor v
                                WITH i.idVendor = v.id
                            WHERE ((i.bankinfo = 0 OR i.bankinfo IS NULL))
                                AND	i.printready = 1
                                AND	i.entityready = 1
                                AND
                                i.deletestatus = 1 AND
                                e.id = " . $id . "
                            GROUP BY i.invoiceId
                            ORDER BY i.dateupdated DESC"
                        );
        $ventity        = $query->getArrayResult();

		//revenue
		$query          	= $em->createQuery(
                            "SELECT
                                r.dateadded,
								r.amount as rev,
								r.description,
								e.entityName
                            FROM ArcanysEasyAppBundle:Revenue r
							LEFT JOIN ArcanysEasyAppBundle:Entity e
							WITH e.id = r.entityId
							WHERE e.id = " . $id . "
							ORDER BY r.dateadded DESC"
                        );
        $revenue       	= $query->getArrayResult();

		//capital
		$query          	= $em->createQuery(
                            "SELECT
                                r.dateadded,
								r.amount as cap,
								r.description,
								f.entityName
							FROM ArcanysEasyAppBundle:Revenueinter r
							LEFT JOIN ArcanysEasyAppBundle:Entity e
							WITH e.id = r.entityIdFrom
							LEFT JOIN ArcanysEasyAppBundle:Entity f
							WITH f.id = r.entityIdTo
							WHERE f.id = " . $id . "
							ORDER BY r.dateadded DESC"
                        );
        $capital       	    = $query->getArrayResult();

		$eregistry          = array_merge($revenue, $ventity, $capital);

		usort($eregistry, function ($a, $b) {
			if($a['dateadded'] == $b['dateadded']){
				return 0;
			}
			return ($a['dateadded'] > $b['dateadded']) ? -1 : 1;
		});

		//print_r($eregistry); exit();

        $paginator          = $this->get('knp_paginator');
        $pagination         = $paginator->paginate(
                                $eregistry, $this->get('request')->query->get('page', 1), 5
                            );

		$entity             = $repository->find($id);
        $repoentitybank     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entitybankinfo');
        $entitybankinfo     = $repoentitybank->findBy( array('entityNumId' => $entity->getId()) );

        if ( strlen($entitybankinfo[0]->getToken()) == 0 ) {
            $token = '';
        } else {
            $token = $entitybankinfo[0]->getToken();
        }

        $repository1        = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entityimages');
        $entityimg          = $repository1->findBy( array('upltoken' => $token) );

		$query          	= $em->createQuery(
                                "SELECT
                                    e
                                FROM ArcanysEasyAppBundle:Entity e
                                WHERE e.id = " . $id
                            );
        $entity       	    = $query->getArrayResult();
		$entity             = $this->get('entity.values.handler')->getCurrentBalance($entity);

        return $this->render('ArcanysEasyAppBundle:AdminentityDashboard:viewEntityDetail.html.twig',array(
            'entity'   => $entity,
            'entimg'   => $entityimg,
            'bankinfo' => $entitybankinfo,
            'ventity'  => $pagination
        ));
    }

    public function registryAction(Request $request)
    {
        $searchKey      = $request->get('searchkey');
        $queryFilter    = "";
        $user           = $this->get('security.context')->getToken()->getUser();
        $session        = $request->getSession();
        $company        = $session->get('company');

        // Advanced search
        $filter  = $request->get('filter');
        if ($filter > 0) {
            $aSearch = "";
            $ctr = 0;

            for ($i = 1; $ctr < $filter; $i++) {
                $search = $request->get('search_' . $i);

                if ($search) {
                    switch($request->get('field_' . $i)) {
                        case 'All Fields':
                            $aSearch .= "
                                 e.entityName LIKE '%" . $search  . "%' OR
                                 eb.bankName LIKE '%" . $search  . "%' OR
                                 eb.bankAcct LIKE '%" . $search  . "%'";
                            break;
                        case 'Entity': $aSearch .= "e.entityName LIKE '%" . $search  . "%' "; break;
                        case 'Bank': $aSearch .= "eb.bankName LIKE '%" . $search  . "%' "; break;
                        case 'Bank Account': $aSearch .= "eb.bankAcct LIKE '%" . $search  . "%' "; break;
                    }

                    if ($ctr < ($filter -1))
                        $aSearch .= " OR ";

                    // The keys are inconsistent so match according
                    // to filter count
                    $ctr++;
                }
            }

            $queryFilter = " WHERE ( ". $aSearch. " ) AND e.company = '" . $company[0] . "'";
        }

        if ($searchKey) {
            $queryFilter    = "
                WHERE (
                    e.entityName LIKE '%{$searchKey}%' OR
                    eb.bankName LIKE '%{$searchKey}%' OR
                    eb.bankAcct LIKE '%{$searchKey}%'
                )
            ";
        }

        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT
                                e.id, e.entityName, eb.entitybankId,
                                eb.entityNumId, eb.bankName, eb.bankAcct, eb.curBalance
                            FROM ArcanysEasyAppBundle:Entity e
                            LEFT JOIN ArcanysEasyAppBundle:Entitybankinfo eb
							WITH e.id = eb.entityNumId
							{$queryFilter}
                            AND e.company = '" . $company[0] . "'
							ORDER BY e.entityName ASC
                            "
                          );
        $entity         = $query->getArrayResult();
		$entity         = $this->get('entity.values.handler')->getCurrentBalanceByBank($entity);

        $paginator      = $this->get('knp_paginator');
        $pagination     = $paginator->paginate(
                                $entity, $this->get('request')->query->get('page', 1), 20
                          );

        $letters        = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
            'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');

        return $this->render('ArcanysEasyAppBundle:AdminentityDashboard:registry.html.twig', array(
            'letters'   => $letters,
            'entity'    => $pagination,
            'searchKey' => $searchKey
        ));
    }

    public function registryviewAction($page)
    {
        /*$repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $entity         = $repository->createQueryBuilder('e')
                                     ->where('e.entityName LIKE :entityName')
                                     ->setParameter('entityName', '' . $page . '%')
                                     ->getQuery()->getResult();*/
        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT
                                e.id, e.entityName,
                                eb.entityNumId, eb.bankName, eb.bankAcct, eb.curBalance
                            FROM ArcanysEasyAppBundle:Entity e
                            LEFT JOIN ArcanysEasyAppBundle:Entitybankinfo eb
                            WITH e.id = eb.entityNumId
                            WHERE e.entityName LIKE '" . $page . "%'
                            ORDER BY e.entityName ASC
                            "
                        );
        $entity         = $query->getArrayResult();
        $entity         = $this->get('entity.values.handler')->getCurrentBalance($entity);

        $paginator      = $this->get('knp_paginator');
        $pagination     = $paginator->paginate(
                                $entity, $this->get('request')->query->get('page', 1), 20
                          );

        $letters        = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
            'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');

        return $this->render('ArcanysEasyAppBundle:AdminentityDashboard:registry.html.twig', array(
            'letters' => $letters,
            'entity'  => $pagination,
            'searchKey' => ""
        ));
    }

    public function searchAction(Request $request)
    {
        $getData        = $request->query->get('searchkey');

        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT
                                e.id, e.entityName,
                                eb.entityNumId, eb.bankName, eb.bankAcct, eb.curBalance
                            FROM ArcanysEasyAppBundle:Entity e
                            LEFT JOIN ArcanysEasyAppBundle:Entitybankinfo eb
                            WITH e.id = eb.entityNumId
                            WHERE e.entityName LIKE '%" . $getData . "%'
                            GROUP BY eb.entityNumId
                            ORDER BY e.entityName ASC
                            "
                        );
        $entity         = $query->getArrayResult();
        $entity         = $this->get('entity.values.handler')->getCurrentBalance($entity);

        $paginator      = $this->get('knp_paginator');
        $pagination     = $paginator->paginate(
                                $entity, $this->get('request')->query->get('page', 1), 20
                          );

        $letters        = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
            'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');

        return $this->render('ArcanysEasyAppBundle:AdminentityDashboard:search.html.twig', array(
            'letters' => $letters,
            'entity'  => $pagination
        ));
    }

    public function deleteAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';

        $em             = $this->getDoctrine()->getManager();
        $entity         = $em->getRepository('ArcanysEasyAppBundle:Entity')->find($getID);

        $em2            = $this->getDoctrine()->getManager();
        $comment        = $em->getRepository('ArcanysEasyAppBundle:Entitycomments')->findOneBy( array('entityId' => $getID) );

        $em->remove($entity);
        $em->flush();

        if ($comment =! NULL) {
            echo 'NULL';
        } else {
            $em2->remove($comment);
            $em2->flush();
        }

        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $query          = $repository->createQueryBuilder('e')
                                     ->orderBy('e.id', 'DESC')
                                     ->setMaxResults(1)
                                     ->getQuery();
        $getentity      = $query->getResult();

        $getEntityID    = '';
        if ($getentity =! NULL) {
            $getEntityID = '';
        } else {
            $getEntityID = $getentity[0]->getId();
        }

        $response        = array( "code" => 100, "success" => true, "id" => $getEntityID );
        return new Response( json_encode($response) );
    }

    public function getyearexpenseAction($id)
    {
        $getFirstMonth  = date("Y-01-01 09:46:29");
        $getCurrentdate = date("Y-m-d H:i:s");

        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT
                                i.invoiceId, i.dueDate, i.amount, i.dateadded,
                                e.entityName, e.id,
                                v.id, v.name
                            FROM ArcanysEasyAppBundle:Entity e
                            JOIN ArcanysEasyAppBundle:Invoice i
                                WITH i.idEntity = e.id
                            JOIN ArcanysEasyAppBundle:Vendor v
                                WITH i.idVendor = v.id
                            WHERE i.dateadded >= '$getFirstMonth' AND i.dateadded <= '$getCurrentdate'
							AND ((i.bankinfo = 0 OR i.bankinfo IS NULL))
							AND	i.printready = 1
							AND	i.entityready = 1
							AND
                                i.deletestatus = 1
                            AND e.id = " . $id . "
                            GROUP BY i.invoiceId"
        );
        $eEntity        = $query->getResult();

        $total = 0;
        foreach ($eEntity as $value) {
            $total += $value['amount'];
        }

        $query			= $em->createQuery(
							"
							SELECT
							  SUM(r.amount)
							FROM
							  ArcanysEasyAppBundle:Revenue r
							WHERE strtodate(r.dateadded, '%m-%d-%Y') >= '$getFirstMonth'
							  AND strtodate(r.dateadded, '%m-%d-%Y') <= '$getCurrentdate'
							  AND r.entityId = " . $id . "
							"
						);
		$rev_total 		= $query->getResult();
		$rev_total = (($rev_total[0][1] != null) ? (int) $rev_total[0][1] : null);

		$query			= $em->createQuery(
							"
							SELECT
							  SUM(c.amount)
							FROM
							  ArcanysEasyAppBundle:Revenueinter c
							WHERE strtodate(c.dateadded, '%m-%d-%Y') >= '$getFirstMonth'
							  AND strtodate(c.dateadded, '%m-%d-%Y') <= '$getCurrentdate'
							  AND c.entityIdTo = " . $id . "
							"
						);
		$cap_total 		= $query->getResult();
		$cap_total = (($cap_total[0][1] != null) ? (int) $cap_total[0][1] : null);

		$response = array( "code" => 100, "success" => true, "total" => $total, "rev_total" => $rev_total, "cap_total" => $cap_total );
        return new JsonResponse($response);
    }

    public function getdatetodateexpenseAction($id)
    {
        $getFromMonth   = (isset($_POST['from'])) ? $_POST['from'] : '';
        $getToMonth     = (isset($_POST['to'])) ? $_POST['to'] : '';

        $FromMonth      = date('Y-m-d 00:00:00', strtotime($getFromMonth));
        $ToMonth        = date('Y-m-d 23:59:59', strtotime($getToMonth));

        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT
                                i.invoiceId, i.dueDate, i.status, i.amount, i.dateadded,
                                e.entityName, e.id,
                                v.id, v.name
                            FROM ArcanysEasyAppBundle:Invoice i
                            JOIN ArcanysEasyAppBundle:Entity e
                                WITH i.idEntity = e.id
                            JOIN ArcanysEasyAppBundle:Vendor v
                                WITH i.idVendor = v.id
                            WHERE i.dateadded >= '$FromMonth' AND i.dateadded <= '$ToMonth'
                            AND e.id = " . $id . "
							AND ((i.bankinfo = 0 OR i.bankinfo IS NULL))
							AND	i.printready = 1
							AND	i.entityready = 1 AND
                                i.deletestatus = 1
                            GROUP BY i.invoiceId"
        );
        $eEntity        = $query->getResult();

        $total = 0;
        foreach ($eEntity as $value) {
            $total += $value['amount'];
        }

        $query			= $em->createQuery(
							"
							SELECT
							  SUM(r.amount)
							FROM
							  ArcanysEasyAppBundle:Revenue r
							WHERE strtodate(r.dateadded, '%m-%d-%Y') >= '$FromMonth'
							  AND strtodate(r.dateadded, '%m-%d-%Y') <= '$ToMonth'
							  AND r.entityId = " . $id . "
							"
						);
		$rev_total 		= $query->getResult();
		$rev_total = (($rev_total[0][1] != null) ? (int) $rev_total[0][1] : null);

		$query			= $em->createQuery(
							"
							SELECT
							  SUM(c.amount)
							FROM
							  ArcanysEasyAppBundle:Revenueinter c
							WHERE strtodate(c.dateadded, '%m-%d-%Y') >= '$FromMonth'
							  AND strtodate(c.dateadded, '%m-%d-%Y') <= '$ToMonth'
							  AND c.entityIdTo = " . $id . "
							"
						);
		$cap_total 		= $query->getResult();
		$cap_total = $cap_total = (($cap_total[0][1] != null) ? (int) $cap_total[0][1] : null);

        $response = array( "code" => 100, "success" => true, "total" => $total, "rev_total" => $rev_total, "cap_total" => $cap_total );
        return new JsonResponse($response);
    }

    public function getmonthexpenseAction($id)
    {
        $getFirstMonth  = date("Y-m-01 00:00:00");
        $getEndMonth    = date("Y-m-d H:i:s");

        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT i.invoiceId, i.dueDate, i.amount, i.dateadded, e.entityName, e.id, v.id, v.name
                            FROM ArcanysEasyAppBundle:Entity e
                            JOIN ArcanysEasyAppBundle:Invoice i WITH i.idEntity = e.id
                            JOIN ArcanysEasyAppBundle:Vendor v WITH i.idVendor = v.id
                            WHERE i.dateadded >= '$getFirstMonth' AND i.dateadded <= '$getEndMonth'
                            AND e.id = " . $id . "
							AND ((i.bankinfo = 0 OR i.bankinfo IS NULL))
							AND	i.printready = 1
							AND	i.entityready = 1 AND
                                i.deletestatus = 1
                            GROUP BY i.invoiceId"
                        );
        $eEntity        = $query->getResult();

        $total = 0;
        foreach ($eEntity as $value) {
            $total += $value['amount'];
        }

        $query			= $em->createQuery(
							"
							SELECT
							  SUM(r.amount)
							FROM
							  ArcanysEasyAppBundle:Revenue r
							WHERE strtodate(r.dateadded, '%m-%d-%Y') >= '$getFirstMonth'
							  AND strtodate(r.dateadded, '%m-%d-%Y') <= '$getEndMonth'
							  AND r.entityId = " . $id . "
							"
						);
		$rev_total 		= $query->getResult();
		$rev_total = (($rev_total[0][1] != null) ? (int) $rev_total[0][1] : null);

		$query			= $em->createQuery(
							"
							SELECT
							  SUM(c.amount)
							FROM
							  ArcanysEasyAppBundle:Revenueinter c
							WHERE strtodate(c.dateadded, '%m-%d-%Y') >= '$getFirstMonth'
							  AND strtodate(c.dateadded, '%m-%d-%Y') <= '$getEndMonth'
							  AND c.entityIdTo = " . $id . "
							"
						);
		$cap_total 		= $query->getResult();
		$cap_total = $cap_total = (($cap_total[0][1] != null) ? (int) $cap_total[0][1] : null);

        $response = array( "success" => true, "total" => $total, "rev_total" => $rev_total, "cap_total" => $cap_total );
        return new JsonResponse($response);
    }

    public function validatechecknoAction()
    {
        $getdata        = (isset($_POST['data'])) ? $_POST['data'] : '';

        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $checkentity    = $repository->createQueryBuilder('e')
                                    ->where('e.startBalance LIKE :startBalance')
                                    ->setParameter('startBalance', '%' . $getdata . '%')
                                    ->getQuery();
        $checkno        = $checkentity->getResult();

//        var_dump($checkno);
        $output = '';
        if ( empty($checkno) ) {
            $output = '0'; //empty
        } else {
//            $output = $checkno[0]->getStartBalance();
            $output = '1'; //true
        }

        $response = array( "success" => true, "output" => $output);
        return new JsonResponse($response);
    }

    public function retrievebankinfoAction()
    {
        $getid          = (isset($_POST['id'])) ? $_POST['id'] : '';

        $repository     = $this->getDoctrine()->getManager();
        $bankinfo       = $repository->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                                     ->findBy( array( 'entityNumId' => $getid ) );

        $bankname = array();
        $acctno   = array();
        $balance  = array();
        $getid    = array();
        foreach ( $bankinfo as $entity ) {
            $bankname[] = $entity->getBankName();
            $acctno[]   = $entity->getBankAcct();
//            $balance[]  = $entity->getCurBalance();
            $balance[]  = $this->get('entity.values.handler')->getCurrentBalanceById($entity->getEntitybankId());
            $getid[]    = $entity->getEntitybankId();
        }

        //var_dump(json_decode(json_encode($data), true)); exit;
        $response = array( "bankname" => $bankname, "acctno" => $acctno, "balance" => $balance, "id" => $getid);
        return new Response(json_encode($response, true));
    }

    public function removeSpaceAction($string)
    {
        $string = str_replace(' ', '', $string);
        return $string;
    }

    public function updatetabentityAction()
    {
        $getid          = (isset($_POST['id'])) ? $_POST['id'] : '';
        $value          = (isset($_POST['value'])) ? $_POST['value'] : '';

        $repository     = $this->getDoctrine()->getManager();
        $tabentity      = $repository->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                                     ->find($getid);

        $tabentity->setEntityTabname($value);
        $repository->persist($tabentity);
        $repository->flush();

        return new JsonResponse(['code' => 100, 'success' => true]);
    }

    public function searchbanknameAction()
    {
        $getdata        = (isset($_POST['data'])) ? $_POST['data'] : '';

        $repository     = $this->getDoctrine()->getManager();
        $sentitybank    = $repository->getRepository('ArcanysEasyAppBundle:Entity');
        $entitybankinfo = $repository->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                                     ->findBy( array( 'bankName' => $getdata ) );

        var_dump($entitybankinfo);

        return new JsonResponse(['code' => 100, 'success' => true]);
    }

    public function createtabentityAction(Request $request)
    {
        if ($request->getMethod() != 'POST') {
            return new JsonResponse(['code' => 100, 'success' => false]);
        }

        $bankname       = $request->get('bankname');
        $bankcontact    = $request->get('bankcontact');
        $bankphonenum   = $request->get('bankphonenum');
        $bankemail      = $request->get('bankemail');
        $bankaddress    = $request->get('bankaddress');
        $city           = $request->get('city');
        $state          = $request->get('state');
        $zipcode        = $request->get('zipcode');
        $bankaccount    = $request->get('bankaccount');
        $bankroute      = $request->get('bankroute');
        $token          = $request->get('token');
        $startbalance   = str_replace(',', '', $request->get('startbalance'));
        $curbalance     = str_replace(',', '', $request->get('curbalance'));
        $comments       = $request->get('comments');

        $input = $request->request->all();
        $textName = $request->get('entityname');
        $em = $this->getDoctrine()->getManager();

        // search entity name
        $repository = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $getEntity  = $repository->findBy( array('entityName' => $textName) );

        // if entity name exist
        if ( empty($getEntity) ) {
            // add entity name
            $entity = new Entity();
            $entity->setEntityName($textName);
            $em->persist($entity);
        } else {
            // update entity name
            $entity = $em->getRepository('ArcanysEasyAppBundle:Entity')->find($getEntity[0]->getId());
            $entity->setEntityName($textName);
            $em->persist($entity);
        }
        // search entity name
        $getEntity  = $repository->findBy( array('entityName' => $textName) );

        /*reset($input);
        $key = key($input);
        unset($input[$key]);
        print_r(json_encode($input));*/

        for ( $i = 0; $i < count($bankname); $i++ ) {

            if ( empty($bankname[$i]) || empty($bankaccount[$i]) ||
                empty($bankcontact[$i]) || empty($bankroute[$i]) ||
                empty($startbalance[$i]) || empty($curbalance[$i]) || empty($comments[$i]) )
                continue;

            $getdatav = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                          ->findBy( array( 'token' => $token[$i] ) );

            if ( empty($getdatav) ) {
                var_dump('sulod');
                $banknameabbr   = preg_replace('~\b(\w)|.~', '$1', $bankname[$i]);
                $bankacctdigits = substr($bankaccount[$i], -4);
                $entitytabname  = strtoupper($banknameabbr) . $bankacctdigits;

                $entitybankinfo = new Entitybankinfo();
                $entitybankinfo->setEntityNumId($getEntity[0]->getId());
                $entitybankinfo->setBankName($bankname[$i]);
                $entitybankinfo->setBankAcct($bankaccount[$i]);
                $entitybankinfo->setBankContact($bankcontact[$i]);
                $entitybankinfo->setEntityContact($bankphonenum[$i]);
                $entitybankinfo->setEntityEmailaddress($bankemail[$i]);
                $entitybankinfo->setEntityAddress($bankaddress[$i]);
                $entitybankinfo->setEntityCity($city[$i]);
                $entitybankinfo->setEntityState($state[$i]);
                $entitybankinfo->setEntityZip($zipcode[$i]);
                $entitybankinfo->setRoutingBalance($bankroute[$i]);
                $entitybankinfo->setStartBalance($startbalance[$i]);
                $entitybankinfo->setCurBalance($curbalance[$i]);
                $entitybankinfo->setEntityTabname($entitytabname);
                $entitybankinfo->setToken($token[$i]);
                $entitybankinfo->setComments($comments[$i]);

                $em->persist($entitybankinfo);
            }
            else {
                var_dump('sulod2');
                $getdata = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                              ->findBy( array( 'entityNumId' => $getEntity[0]->getId() ) );

                foreach ( $getdata as $key => $value ) {
                    if (!isset($value->entitybankId)) {
                        continue;
                    }

                    $bankInfo = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')->find($value->entitybankId);
                    $bankInfo->setBankName($value->bankname);
                    $bankInfo->setBankAcct($value->bankaccount);
                    $bankInfo->setBankContact($value->bankcontact);
                    $bankInfo->setEntityContact($value->bankphonenum);
                    $bankInfo->setEntityEmailaddress($value->bankemail);
                    $bankInfo->setEntityAddress($value->bankaddress);
                    $bankInfo->setEntityCity($value->city);
                    $bankInfo->setEntityState($value->state);
                    $bankInfo->setEntityZip($value->zipcode);
                    $bankInfo->setRoutingBalance($value->bankroute);
                    $bankInfo->setStartBalance($value->startbalance);
                    $bankInfo->setCurBalance($value->curbalance);
                    $bankInfo->setEntityTabname($value->entitytabname);
                    $bankInfo->setComments($value->comments);

                    $em->persist($bankInfo);
                }
            }
        }
        $em->flush();

        /*foreach(json_encode($input) as $key => $value) {
            var_dump($value);
            if (!isset($value->entitybankId)) {
                continue;
            }

            if (!$value->entitybankId) {
                if ($value->bankname) {
                    $entityBankInfo = new Entitybankinfo();
                    $entityBankInfo->setEntityNumId($getEntity[0]->getId());
                    $entityBankInfo->setBankName($value->bankname);
                    $entityBankInfo->setBankAcct($value->bankaccount);
                    $entityBankInfo->setBankContact($value->bankcontact);
                    $entityBankInfo->setEntityContact($value->bankphonenum);
                    $entityBankInfo->setEntityEmailaddress($value->bankemail);
                    $entityBankInfo->setEntityAddress($value->bankaddress);
                    $entityBankInfo->setEntityCity($value->city);
                    $entityBankInfo->setEntityState($value->state);
                    $entityBankInfo->setEntityZip($value->zipcode);
                    $entityBankInfo->setRoutingBalance($value->bankroute);
                    $entityBankInfo->setStartBalance($value->startbalance);
                    $entityBankInfo->setCurBalance($value->curbalance);
                    $entityBankInfo->setComments($value->comments);

                    $em->persist($entityBankInfo);
                }
                continue;
            }

            $bankInfo = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')->find($value->entitybankId);
            $bankInfo->setBankName($value->bankname);
            $bankInfo->setBankAcct($value->bankaccount);
            $bankInfo->setBankContact($value->bankcontact);
            $bankInfo->setEntityContact($value->bankphonenum);
            $bankInfo->setEntityEmailaddress($value->bankemail);
            $bankInfo->setEntityAddress($value->bankaddress);
            $bankInfo->setEntityCity($value->city);
            $bankInfo->setEntityState($value->state);
            $bankInfo->setEntityZip($value->zipcode);
            $bankInfo->setRoutingBalance($value->bankroute);
            $bankInfo->setStartBalance($value->startbalance);
            $bankInfo->setCurBalance($value->curbalance);
            $bankInfo->setComments($value->comments);

            $em->persist($bankInfo);
        }*/

        /*try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['code' => 100, 'success' => false, 'msg' => $e->getMessage()]);
        }*/

        return new JsonResponse(['code' => 100, 'success' => true]);
    }

    public function deleteimageAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';

        $invoiceimgrepo = $this->getDoctrine()->getManager();
        $invoiceimg     = $invoiceimgrepo->getRepository('ArcanysEasyAppBundle:Entityimages')->find($getID);

        if (file_exists($invoiceimg->getFileName())) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $this->container->getParameter('target_folder') . $invoiceimg->getFileName());
        }

        $invoiceimgrepo->remove($invoiceimg);
        $invoiceimgrepo->flush();

        $response       = array( "code" => 100, "success" => true );
        return new JsonResponse($response);
    }

    public function imageAction(Request $request)
    {
        $entityId = $request->get('entityId');
        $bankno = $request->get('bankno');

        $em = $this->getDoctrine()->getManager();
        $entityImages = $em->getRepository('ArcanysEasyAppBundle:Entityimages')
            ->findBy(['entityId' => $entityId, 'bankno' => $bankno]);

        $images = [];
        foreach($entityImages as $key => $image) {
            $images[] = [
                'id' => $image->getId(),
                'fileName' => $image->getFilename()
            ];
        }

        return new JsonResponse(['code' => 100, 'success' => true, 'images' => $images]);
    }

}
