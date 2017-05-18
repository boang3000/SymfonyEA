<?php

namespace Arcanys\EasyAppBundle\Controller;

use Arcanys\EasyAppBundle\Entity\Revenue;
use Arcanys\EasyAppBundle\Entity\Revenuewire;
use Arcanys\EasyAppBundle\Entity\Revenueinter;
use Arcanys\EasyAppBundle\Entity\RevenueImages;
use Arcanys\EasyAppBundle\Form\Type\RevenueFormType;
use Arcanys\EasyAppBundle\Form\Type\EditRevenueFormType;
use Arcanys\EasyAppBundle\Form\Type\WireFormType;
use Arcanys\EasyAppBundle\Form\Type\EditWireFormType;
use Arcanys\EasyAppBundle\Form\Type\InterFormType;
use Arcanys\EasyAppBundle\Form\Type\EditInterFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class RevenueController extends Controller
{
    public function indexRevenueAction()
    {
		$request = $this->get('request');
        $session = $request->getSession();
        $company = $session->get('company');
		if($request->query->get('entity_id')) {
			$entity_id = $request->query->get('entity_id');
			$where = "WHERE r.entityId = $entity_id AND r.company = '" . $company[0] . "'";
		} else {
			$entity_id = 0;
			$where = "WHERE r.company = '" . $company[0] . "'";
		}
		$em             = $this->getDoctrine()->getManager();
        $q          	= $em->createQuery(
                            "SELECT
                                r,
								e.entityName
                            FROM ArcanysEasyAppBundle:Revenue r
							LEFT JOIN ArcanysEasyAppBundle:Entity e
							WITH e.id = r.entityId
							$where
                            ORDER BY r.dateadded DESC"
                        );
        $revenue       	= $q->getResult();
        $paginator     	= $this->get('knp_paginator');
        $pagination    	= $paginator->paginate(
                          $revenue, $this->get('request')->query->get('revenue', 1), 10,
                            array('pageParameterName' => 'revenue')
                        );
		if($request->query->get('entity_id')) {
			$pagination->setParam('entity_id', $request->query->get('entity_id'));
		}
		
		$entitylist    	= $this->getDoctrine()->getManager()
                               ->createQuery(
                                    "SELECT e.id, e.entityName
                                     FROM ArcanysEasyAppBundle:Entity e
                                     WHERE e.company = '" . $company[0] . "'"
                               )->getResult();
		return $this->render('ArcanysEasyAppBundle:Revenue:revenue.html.twig', array(
			'rev'        => $pagination,
			'entity'	 => $entitylist,
			'curr_entid' => $entity_id,
		));
    }
	
	public function addRevenueAction()
    {
		$request = $this->get('request');
        $session = $request->getSession();
        $company = $session->get('company');
		$form    = $this->createForm(new RevenueFormType($company));
        $session = $request->getSession();
        $company = $session->get('company');
        $user    = $this->get('security.context')->getToken()->getUser();
		$form->handleRequest($request);
		if ($form->isValid()) {
			$formval = $request->request->all();
            //var_dump($formval);
			$revenue = new Revenue();
			$revenue->setDateadded($formval['revenue']['dateadded']);
			$revenue->setEntityId($formval['revenue']['entity']);
			$revenue->setEntitybanknameId($formval['bankname']);
			$revenue->setAmount($formval['revenue']['amount']);
			$revenue->setDescription($formval['revenue']['description']);
			$revenue->setUpltoken($formval['token']);
			$revenue->setCompany($user->getCompany());

			$em = $this->getDoctrine()->getManager();
            $em->persist($revenue);
            $em->flush();
			
			return $this->redirect($this->generateUrl('Revenue_index_revenue'));
		}
		
		return $this->render('ArcanysEasyAppBundle:Revenue:revenue-add.html.twig', array(
			'form'      => $form->createView(),
			'checknum'  => uniqid(),
		));
    }
	
	public function editRevenueAction(Revenue $revenue)
    {
		$request  = $this->get('request');
        $session  = $request->getSession();
        $company  = $session->get('company');
		$em       = $this->getDoctrine()->getManager();
        $revinfo  = $em->getRepository('ArcanysEasyAppBundle:Revenue')->find($revenue->getId());
        $enbanks  = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                       ->findBy( array( 'entityNumId' => $revinfo->getEntityId() ) );
        $bankinfo = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                       ->findBy( array( 'entitybankId' => $revinfo->getEntitybanknameId() ) );
		$form     = $this->createForm(new EditRevenueFormType($company), $revenue);

		if ( $request->getMethod() == 'POST' ) {
			$formval = $request->request->all();
			$revenue->setDateadded($formval['revenue']['dateadded']);
			$revenue->setAmount($formval['revenue']['amount']);
			$revenue->setEntityId($formval['revenue']['entityid']);
            $revenue->setEntitybanknameId($formval['bankname']);
			$revenue->setDescription($formval['revenue']['description']);
			$em->flush();
			return $this->redirect($this->generateUrl('Revenue_index_revenue'));			
		}
				
		return $this->render('ArcanysEasyAppBundle:Revenue:revenue-edit.html.twig', array(
			'form'      => $form->createView(),
			'entid'		=> $revenue->getEntityId(),
            'bankinfo'  => $bankinfo,
            'enbanks'   => $enbanks,
			'revid'     => $revenue->getId(),
		));
    }
	
	public function uploadAction()
    {
        $request            = $this->get('request');
        $checknum           = $request->get('token');
        
		$targetFolder = $this->container->getParameter('target_folder'); // Relative to the root

        if (!empty($_FILES)) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
            $targetFile = rtrim($targetPath,'/') . '/' . md5(date("mdYGis")) . $_FILES['Filedata']['name'];

            // Validate the file type
            $fileTypes = array('jpg','jpeg','gif','png'); // File extensions
            $fileParts = pathinfo($_FILES['Filedata']['name']);

            if (in_array($fileParts['extension'], $fileTypes)) {
                move_uploaded_file($tempFile, $targetFile);
                $revenueimage = new RevenueImages();
                $revenueimage->setFileName(md5(date("mdYGis")) . $_FILES['Filedata']['name']);
                $revenueimage->setStatus('1');
                $revenueimage->setUpltoken($checknum);

                $em2 = $this->getDoctrine()->getManager();
                $em2->persist($revenueimage);
                $em2->flush();
            } else {
                echo 'Invalid file type.';
            }
        }

        $response = array( "code" => 100, "success" => true );
        return new JsonResponse($response);
    }
	
	public function retrievenewimageAction()
    {
        $request            = $this->get('request');
        $token              = $request->get('uplToken');

        $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:RevenueImages');
        $getRevenueimg      = $repository->findBy( array('upltoken' => $token) );

        $str    = array();
        $id     = array();
        foreach ($getRevenueimg as $key => $value) {
            end($getRevenueimg);
            if ($key === key($getRevenueimg))
                $str[]  = $value->getFileName();
            $id[]   = $value->getId();
        }

        $response           = array( "success" => true, "image" => $str, "id" => $id );
        return new JsonResponse($response);
    }
	
	public function retrieveallimageAction()
    {
        $request            = $this->get('request');
        $token              = $request->get('uplToken');

        $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:RevenueImages');
        $getRevenueimg      = $repository->findBy( array('upltoken' => $token) );

        $str    = array();
        $id     = array();
        foreach ($getRevenueimg as $key => $value) {
            end($getRevenueimg);
            //if ($key === key($getRevenueimg))
                $str[]  = $value->getFileName();
            $id[]   = $value->getId();
        }

        $response           = array( "success" => true, "image" => $str, "id" => $id );
        return new JsonResponse($response);
    }
	
	public function indexWireAction()
    {
        $request = $this->get('request');
        $session = $request->getSession();
        $company = $session->get('company');
		if($request->query->get('entity_id')) {
			$entity_id = $request->query->get('entity_id');
			$where = "WHERE r.entityId = $entity_id AND r.company = '" . $company[0] . "'";
		} else {
			$entity_id = 0;
			$where = "WHERE r.company = '" . $company[0] . "'";
		}
		$em             = $this->getDoctrine()->getManager();
        $q          	= $em->createQuery(
                            "SELECT
                                r,
								e.entityName
                            FROM ArcanysEasyAppBundle:Revenuewire r
							LEFT JOIN ArcanysEasyAppBundle:Entity e
							WITH e.id = r.entityId
							$where
                            ORDER BY r.dateadded DESC"
                        );
        $revenue       	= $q->getArrayResult();
		$revenue        = $this->get('entity.values.handler')->getCurrentBalanceWireEntity($revenue);

        $paginator     	= $this->get('knp_paginator');
        $pagination    	= $paginator->paginate(
                          $revenue, $this->get('request')->query->get('wire', 1), 10,
                            array('pageParameterName' => 'wire')
                        );
		if($request->query->get('entity_id')) {
			$pagination->setParam('entity_id', $request->query->get('entity_id'));
		}
		
		$entitylist    	= $this->getDoctrine()->getManager()
                               ->createQuery(
                                    "SELECT e.id, e.entityName
                                     FROM ArcanysEasyAppBundle:Entity e
                                     WHERE e.company = '" . $company[0] . "'"
                               )->getResult();

		return $this->render('ArcanysEasyAppBundle:Revenue:wire.html.twig', array(
			'rev'        => $pagination,
			'entity'	 => $entitylist,
			'curr_entid' => $entity_id,
		));
    }
	
	public function addWireAction()
    {
        $request = $this->get('request');
        $session = $request->getSession();
        $company = $session->get('company');
		$form = $this->createForm(new WireFormType($company));
		$form->handleRequest($request);
		$em = $this->getDoctrine()->getManager();
            		
		if ($form->isValid()) {
			$formval = $request->request->all();
			$revenue = new Revenuewire();
			$revenue->setDateadded($formval['revenue']['dateadded']);
			$revenue->setEntityId($formval['revenue']['entity']);
			$revenue->setEntitybanknameId($formval['bankname']);
			$revenue->setWiretype($formval['revenue']['wiretype']);
			$revenue->setAmount($formval['revenue']['amount']);
			$revenue->setDescription($formval['revenue']['description']);
			$revenue->setUpltoken($formval['token']);
			$revenue->setCompany($company[0]);

			$em->persist($revenue);
            $em->flush();
			
			return $this->redirect($this->generateUrl('Revenue_index_wire'));
		}

		$entity_list = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                          ->createQueryBuilder('e')
                          ->select('e.entitybankId, e.entityNumId, e.bankName, e.bankAcct')
                          ->getQuery()
                          ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
							
		foreach($entity_list as $key => $val) {
			$entity_list_bal[intval($val['entitybankId'])]['cur_bal']           = $this->get('entity.values.handler')->getCurrentBalanceById($val['entitybankId']);
			$entity_list_bal[intval($val['entitybankId'])]['bank_name']         = $val['bankName'];
			$entity_list_bal[intval($val['entitybankId'])]['bank_account_no']   = substr($val['bankAcct'], -4);
		}
		
		return $this->render('ArcanysEasyAppBundle:Revenue:wire-add.html.twig', array(
			'form'          => $form->createView(),
			'checknum'      => uniqid(),
			'entity_list'   => $entity_list_bal,
		));
    }
	
	public function editWireAction(Revenuewire $revenue)
    {
		$request    = $this->get('request');
        $session    = $request->getSession();
        $company    = $session->get('company');
		$em         = $this->getDoctrine()->getManager();
        $revinfo    = $em->getRepository('ArcanysEasyAppBundle:Revenuewire')->find($revenue->getId());
        $enbanks    = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                         ->findBy( array( 'entityNumId' => $revinfo->getEntityId() ) );
        $bankinfo   = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                         ->findBy( array( 'entitybankId' => $revinfo->getEntitybanknameId() ) );
        $curbal     = $this->get('entity.values.handler')->getCurrentBalanceById($bankinfo[0]->getEntitybankId());
		$form       = $this->createForm(new EditWireFormType($company), $revenue);
		
		if ( $request->getMethod() == 'POST' ) {
			$formval = $request->request->all();
			$revenue->setDateadded($formval['revenue']['dateadded']);
			$revenue->setEntityId($formval['revenue']['entityid']);
            $revenue->setEntitybanknameId($formval['bankname']);
			$revenue->setWiretype($formval['revenue']['wiretype']);
			$revenue->setAmount($formval['revenue']['amount']);
			$revenue->setDescription($formval['revenue']['description']);
			$revenue->setStatus(0);
			$em->flush();
			return $this->redirect($this->generateUrl('Revenue_index_wire'));			
		}
		
		$entity_list = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
					      ->createQueryBuilder('e')
                          ->select('e.entitybankId, e.entityNumId, e.bankName, e.bankAcct')
						  ->getQuery()
						  ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
							
		foreach($entity_list as $key => $val) {
			$entity_list_bal[intval($val['entitybankId'])]['cur_bal']           = $this->get('entity.values.handler')->getCurrentBalanceById($val['entitybankId']);
			$entity_list_bal[intval($val['entitybankId'])]['bank_name']         = $val['bankName'];
			$entity_list_bal[intval($val['entitybankId'])]['bank_account_no']   = substr($val['bankAcct'], -4);
		}
				
		return $this->render('ArcanysEasyAppBundle:Revenue:wire-edit.html.twig', array(
			'form'          => $form->createView(),
			'entid'		    => $revenue->getEntityId(),
			'wireid'        => $revenue->getId(),
			'wirestatus'    => $revenue->getStatus(),
            'bankinfo'      => $bankinfo,
            'enbanks'       => $enbanks,
            'curbal'        => $curbal,
			'entity_list'   => $entity_list_bal,
		));
    }
	
	public function statusWireAction(Revenuewire $wire, $status)
    {
		$em = $this->getDoctrine()->getManager();
		$wire->setStatus($status);
		$em->flush();
		
		return $this->redirect($this->generateUrl('Revenue_index_wire'));
    }
	
	public function indexInterAction()
    {
		$request = $this->get('request');
        $session = $request->getSession();
        $company = $session->get('company');
		if($request->query->get('entity_id_from') || $request->query->get('entity_id_to')) {
			$curr_entid_from = $request->query->get('entity_id_from');
			$curr_entid_to = $request->query->get('entity_id_to');
			if($curr_entid_from != 0 && $curr_entid_to != 0) {
				$where = "WHERE r.entityIdFrom = $curr_entid_from AND r.entityIdTo = $curr_entid_to AND r.company = '" . $company[0] . "'";
			}
			if($curr_entid_from != 0 && $curr_entid_to == 0) {
				$where = "WHERE r.entityIdFrom = $curr_entid_from AND r.company = '" . $company[0] . "'";
			}
			if($curr_entid_from == 0 && $curr_entid_to != 0) {
				$where = "WHERE r.entityIdTo = $curr_entid_to AND r.company = '" . $company[0] . "'";
			}
			//print_r($curr_entid_from . $curr_entid_to); exit();
		} else {
			$curr_entid_from = 0;
			$curr_entid_to = 0;
			$where = "WHERE r.company = '" . $company[0] . "'";
		}
		$em             = $this->getDoctrine()->getManager();
        $q          	= $em->createQuery(
                            "SELECT
                                r,
								e.entityName as entityNameFrom,
								f.entityName as entityNameTo
							FROM ArcanysEasyAppBundle:Revenueinter r
							LEFT JOIN ArcanysEasyAppBundle:Entity e
							WITH e.id = r.entityIdFrom
							LEFT JOIN ArcanysEasyAppBundle:Entity f
							WITH f.id = r.entityIdTo
							$where
                            ORDER BY r.dateadded DESC"
                        );
        $revenue       	= $q->getResult();

        $paginator     	= $this->get('knp_paginator');
        $pagination    	= $paginator->paginate(
                          $revenue, $this->get('request')->query->get('inter', 1), 10,
                            array('pageParameterName' => 'inter')
                        );
		if($request->query->get('entity_id')) {
			$pagination->setParam('entity_id', $request->query->get('entity_id'));
		}
		
		$entitylist    	= $this->getDoctrine()->getManager()
                               ->createQuery(
                                    "SELECT e.id, e.entityName
                                     FROM ArcanysEasyAppBundle:Entity e
                                     WHERE e.company = '" . $company[0] . "'"
                               )->getResult();
		//var_dump($pagination); exit();
		return $this->render('ArcanysEasyAppBundle:Revenue:inter.html.twig', array(
			'rev'        => $pagination,
			'entity'	 => $entitylist,
			'curr_entid_from' => $curr_entid_from,
			'curr_entid_to' => $curr_entid_to,
		));
    }
	
	public function addInterAction()
    {
		$request = $this->get('request');
        $session = $request->getSession();
        $company = $session->get('company');
		$form = $this->createForm(new InterFormType($company));
		$form->handleRequest($request);
		$em = $this->getDoctrine()->getManager();
            		
		if ($form->isValid()) {
			$formval = $request->request->all();
			$revenue = new RevenueInter();
			$revenue->setDateadded($formval['revenue']['dateadded']);
			$revenue->setEntityIdFrom($formval['revenue']['entity_from']);
			$revenue->setEntitybanknameIdFrom($formval['bankname']);
			$revenue->setEntityIdTo($formval['revenue']['entity_to']);
			$revenue->setEntitybanknameIdTo($formval['bankname2']);
			$revenue->setAmount($formval['revenue']['amount']);
			$revenue->setDescription($formval['revenue']['description']);
			$revenue->setUpltoken($formval['token']);
			$revenue->setCompany($company[0]);

			$em->persist($revenue);
            $em->flush();
			
			return $this->redirect($this->generateUrl('Revenue_index_inter'));
		}
		
		$entity_list = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
						  ->createQueryBuilder('e')
						  ->select('e.entitybankId, e.entityNumId, e.bankName, e.bankAcct')
						  ->getQuery()
						  ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
							
		foreach($entity_list as $key => $val) {
			$entity_list_bal[intval($val['entitybankId'])]['cur_bal']           = $this->get('entity.values.handler')->getCurrentBalanceById($val['entitybankId']);
			$entity_list_bal[intval($val['entitybankId'])]['bank_name']         = $val['bankName'];
			$entity_list_bal[intval($val['entitybankId'])]['bank_account_no']   = substr($val['bankAcct'], -4);
		}
		
		return $this->render('ArcanysEasyAppBundle:Revenue:inter-add.html.twig', array(
			'form'          => $form->createView(),
			'checknum'      => uniqid(),
			'entity_list'   => $entity_list_bal,
		));
    }
	
	public function editInterAction(Revenueinter $revenue)
    {
		$request    = $this->get('request');
        $session    = $request->getSession();
        $company    = $session->get('company');
		$em         = $this->getDoctrine()->getManager();
        $revinfo    = $em->getRepository('ArcanysEasyAppBundle:Revenueinter')
                         ->find($revenue->getId());

        // display list of banks selected by the Entity From
        $enbanksFrm = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                         ->findBy( array( 'entityNumId' => $revinfo->getEntityIdFrom() ) );
        // display list of banks selected by the Entity To
        $enbanksTo  = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                         ->findBy( array( 'entityNumId' => $revinfo->getEntityIdTo() ) );

        $bankFrom   = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                         ->findBy( array( 'entitybankId' => $revinfo->getEntitybanknameIdFrom() ) );
        $bankTo     = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                         ->findBy( array( 'entitybankId' => $revinfo->getEntitybanknameIdTo() ) );

        $curbal     = $this->get('entity.values.handler')->getCurrentBalanceById($bankFrom[0]->getEntitybankId());
		$form       = $this->createForm(new EditInterFormType($company), $revenue);
		
		if ( $request->getMethod() == 'POST' ) {
			$formval = $request->request->all();
			$revenue->setDateadded($formval['revenue']['dateadded']);
			$revenue->setEntityIdFrom($formval['revenue']['entityIdFrom']);
            $revenue->setEntitybanknameIdFrom($formval['bankname']);
			$revenue->setEntityIdTo($formval['revenue']['entityIdTo']);
            $revenue->setEntitybanknameIdTo($formval['bankname2']);
			$revenue->setAmount($formval['revenue']['amount']);
			$revenue->setDescription($formval['revenue']['description']);
			$em->flush();
			return $this->redirect($this->generateUrl('Revenue_index_inter'));			
		}
		
		$entity_list = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
							->createQueryBuilder('e')
							->select('e.entitybankId, e.entityNumId, e.bankName, e.bankAcct')
							->getQuery()
							->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
							
		foreach($entity_list as $key => $val) {
			$entity_list_bal[intval($val['entitybankId'])]['cur_bal']           = $this->get('entity.values.handler')->getCurrentBalanceById($val['entitybankId']);
			$entity_list_bal[intval($val['entitybankId'])]['bank_name']         = $val['bankName'];
			$entity_list_bal[intval($val['entitybankId'])]['bank_account_no']   = substr($val['bankAcct'], -4);
		}
				
		return $this->render('ArcanysEasyAppBundle:Revenue:inter-edit.html.twig', array(
			'form'          => $form->createView(),
			'entid_from'	=> $revenue->getEntityIdFrom(),
			'entid_to'		=> $revenue->getEntityIdTo(),
			'entity_list'   => $entity_list_bal,
            'bankFrom'      => $bankFrom,
            'bankTo'        => $bankTo,
            'enbanksFrm'    => $enbanksFrm,
            'enbanksTo'     => $enbanksTo,
            'curbal'        => $curbal,
			'interid'       => $revenue->getId(),
		));
    }
	
	public function getyearexpenseAction()
    {
        $getFirstMonth  = date("Y-01-01 H:i:s");
        $getCurrentdate = date("Y-m-d H:i:s");

        $em             = $this->getDoctrine()->getManager();
        $query			= $em->createQuery(
							"
							SELECT 
							  SUM(r.amount) 
							FROM
							  ArcanysEasyAppBundle:Revenue r 
							WHERE strtodate(r.dateadded, '%m-%d-%Y') >= '$getFirstMonth'
							  AND strtodate(r.dateadded, '%m-%d-%Y') <= '$getCurrentdate'
							"
						);
		$rev_total 		= $query->getResult();
		$rev_total = $rev_total = (($rev_total[0][1] != null) ? (float) $rev_total[0][1] : null);
		
		$response = array( "code" => 100, "success" => true, "rev_total" => $rev_total );
        return new JsonResponse($response);
    }

    public function getmonthexpenseAction()
    {
        $getFirstMonth  = date("Y-m-01 00:00:00");
        $getEndMonth    = date("Y-m-d H:i:s");

        $em             = $this->getDoctrine()->getManager();
        $query			= $em->createQuery(
							"
							SELECT 
							  SUM(r.amount) 
							FROM
							  ArcanysEasyAppBundle:Revenue r 
							WHERE strtodate(r.createAt, '%m-%d-%Y') >= '$getFirstMonth'
							  AND strtodate(r.createAt, '%m-%d-%Y') <= '$getEndMonth'
							"
						);
		$rev_total 		= $query->getResult();
		$rev_total = $rev_total = (($rev_total[0][1] != null) ? (float) $rev_total[0][1] : null);
		
		$response = array( "code" => 100, "success" => true, "rev_total" => $rev_total );
        return new JsonResponse($response);
    }

    public function getdatetodateexpenseAction()
    {
        $getFromMonth   = (isset($_POST['from'])) ? $_POST['from'] : '';
        $getToMonth     = (isset($_POST['to'])) ? $_POST['to'] : '';

        $FromMonth      = date('Y-m-d 00:00:00', strtotime($getFromMonth));
        $ToMonth        = date('Y-m-d 23:59:59', strtotime($getToMonth));

        $em             = $this->getDoctrine()->getManager();
        $query			= $em->createQuery(
							"
							SELECT 
							  SUM(r.amount) 
							FROM
							  ArcanysEasyAppBundle:Revenue r 
							WHERE strtodate(r.dateadded, '%m-%d-%Y') >= '$FromMonth'
							  AND strtodate(r.dateadded, '%m-%d-%Y') <= '$ToMonth'
							"
						);
		$rev_total 		= $query->getResult();
		$rev_total = $rev_total = (($rev_total[0][1] != null) ? (float) $rev_total[0][1] : null);
		
		$response = array( "code" => 100, "success" => true, "rev_total" => $rev_total );
        return new JsonResponse($response);
    }
	
	public function deleteAction($id, $where, $landing)
    {
		$em = $this->getDoctrine()->getManager();
		$entity = $em->getRepository("ArcanysEasyAppBundle:$where")->findOneBy(array('id'=> $id));
		$em->remove($entity);
		$em->flush();
		
		return $this->redirect($this->generateUrl("Revenue_index_$landing"));
    }

    public function entitybankinfoAction()
    {
        $getID         = (isset($_POST['value'])) ? $_POST['value'] : '';

        $request       = $this->container->get('request');
        $url           = $request->headers->get('referer');
        $url_path      = parse_url($url, PHP_URL_PATH);
        //$basename      = pathinfo($url_path, PATHINFO_BASENAME);
        $segments      = explode('/', $url_path);

        $repository    = $this->getDoctrine()->getManager();
        $getbankinfo   = $repository->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                                    ->findBy( array( 'entityNumId' => $getID ) );

        $getvalue  = '<label for="' . $segments[3] .'" class="required">Entity Bank info</label>';
        $getvalue .= '<select id="' . $segments[3] .'" name="bankname" required="required" class="validate[required]">';
        $getvalue .= '<option value="">Select Bank Name</option>';
        foreach ($getbankinfo as $bankinfo) {
            $getvalue .= '<option value="' . $bankinfo->getEntitybankId() . '">' . $bankinfo->getBankName() . '</option>';
        }
        $getvalue .= '</select>';

        $response = array( "code" => 100, "success" => true, "info" => $getvalue );
        return new JsonResponse($response);
    }

    public function entitybankinfo2Action()
    {
        $getID         = (isset($_POST['value'])) ? $_POST['value'] : '';

        $request       = $this->container->get('request');
        $url           = $request->headers->get('referer');
        $url_path      = parse_url($url, PHP_URL_PATH);
        $segments      = explode('/', $url_path);

        $repository    = $this->getDoctrine()->getManager();
        $getbankinfo   = $repository->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                                    ->findBy( array( 'entityNumId' => $getID ) );

        $getvalue  = '<label for="' . $segments[3] .'2" class="required">Entity Bank info</label>';
        $getvalue .= '<select id="' . $segments[3] .'2" name="bankname2" required="required" class="validate[required]">';
        $getvalue .= '<option value="">Select Bank Name</option>';
        foreach ($getbankinfo as $bankinfo) {
            $getvalue .= '<option value="' . $bankinfo->getEntitybankId() . '">' . $bankinfo->getBankName() . '</option>';
        }
        $getvalue .= '</select>';

        $response = array( "code" => 100, "success" => true, "info" => $getvalue );
        return new JsonResponse($response);
    }
}
