<?php

namespace Arcanys\EasyAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class RegistryController extends Controller
{
	public function indexAction(Request $request)
    {
        // Advanced search
        $filter  = $request->get('filter');
        $queryFilter = "";
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
                                 e.bankName LIKE '%" . $search  . "%' OR
                                 e.bankAcct LIKE '%" . $search  . "%' OR
                                 v.name LIKE '%" . $search  . "%'";
                            break;
                        case 'Entity': $aSearch .= "e.entityName LIKE '%" . $search  . "%' "; break;
                        case 'Bank': $aSearch .= "e.bankName LIKE '%" . $search  . "%' "; break;
                        case 'Bank Account': $aSearch .= "e.bankAcct LIKE '%" . $search  . "%' "; break;
                        case 'Vendor': $aSearch .= "v.name LIKE '%" . $search  . "%' "; break;
                    }

                    if ($ctr < ($filter -1))
                        $aSearch .= " OR ";

                    // The keys are inconsistent so match according
                    // to filter count
                    $ctr++;
                }
            }

            $queryFilter = " AND ( ". $aSearch. " ) ";
        }

		//invoice
		$em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT
                                i.invoiceId, i.invoicenumber, i.status, i.description, i.status, i.dueDate, i.checkNo, i.amount, dateformat(i.dateadded, '%m-%d-%Y') as dateadded, i.dateupdated,
                                e.entityName, e.id, e.bankName, e.curBalance, e.bankAcct,
                                eb.bankName, eb.entityNumId, eb.bankAcct,
                                ib.id, ib.invoiceinfoId, ib.entitybankinfoId, ib.entityId,
                                v.id, v.name
                            FROM ArcanysEasyAppBundle:Entity e
                            JOIN ArcanysEasyAppBundle:Invoice i
                                WITH i.idEntity = e.id
                            JOIN ArcanysEasyAppBundle:Vendor v
                                WITH i.idVendor = v.id
                            JOIN ArcanysEasyAppBundle:Invoiceebankinfo ib
                                WITH ib.entityId = i.idEntity
                            JOIN ArcanysEasyAppBundle:Entitybankinfo eb
                                WITH eb.entityNumId = ib.entityId
                            WHERE
                                  i.status NOT IN (2, 22, 9)
                            {$queryFilter}
                            GROUP BY i.invoiceId
                            ORDER BY i.dateupdated DESC" //i.status NOT IN (1, 11, 2, 22, 9)
                        );
        $invoice        = $query->getArrayResult();
		
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
							ORDER BY r.dateadded DESC"
                        );
        $capital       	= $query->getArrayResult();
		
		$mregistry = array_merge($revenue, $invoice, $capital);
		
		usort($mregistry, function ($a, $b) {
            if ($a == $b) {
				return 0;
			}
			return strtolower($a['entityName']) > strtolower($b['entityName']);
		});

		$paginator   = $this->get('knp_paginator');
        $pagination  = $paginator->paginate(
            $mregistry, $this->get('request')->query->get('page', 1), 10
        );

        return $this->render('ArcanysEasyAppBundle:Masterregistry:index.html.twig', array(
            'data'  => $pagination
        ));
    }

    public function getyearexpenseAction()
    {
        $getFirstMonth  = date("Y-01-01 H:i:s");
        $getCurrentdate = date("Y-m-d H:i:s");

        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT i.invoiceId, i.dueDate, i.amount, i.dateadded, e.entityName, e.id, v.id, v.name
                            FROM ArcanysEasyAppBundle:Entity e
                            JOIN ArcanysEasyAppBundle:Invoice i WITH i.idEntity = e.id
                            JOIN ArcanysEasyAppBundle:Vendor v WITH i.idVendor = v.id
                            WHERE i.dateadded >= '$getFirstMonth' AND i.dateadded <= '$getCurrentdate'
                            AND i.status NOT IN (1, 11, 2, 22, 9)
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
							"
						);
		$rev_total 		= $query->getResult();
		$rev_total = $rev_total = (($rev_total[0][1] != null) ? (int) $rev_total[0][1] : null);
		
		$query			= $em->createQuery(
							"
							SELECT 
							  SUM(c.amount) 
							FROM
							  ArcanysEasyAppBundle:Revenueinter c 
							WHERE strtodate(c.dateadded, '%m-%d-%Y') >= '$getFirstMonth'
							  AND strtodate(c.dateadded, '%m-%d-%Y') <= '$getCurrentdate'
							"
						);
		$cap_total 		= $query->getResult();
		$cap_total = $cap_total = (($cap_total[0][1] != null) ? (int) $cap_total[0][1] : null);
		
		$response = array( "code" => 100, "success" => true, "total" => $total, "rev_total" => $rev_total, "cap_total" => $cap_total );
        return new JsonResponse($response);
    }

    public function getmonthexpenseAction()
    {
        $getFirstMonth  = date("Y-m-01 00:00:00");
        $getEndMonth    = date("Y-m-d H:i:s");

        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT i.invoiceId, i.dueDate, i.amount, i.dateadded, e.entityName, e.id, v.id, v.name
                            FROM ArcanysEasyAppBundle:Invoice i
                            JOIN ArcanysEasyAppBundle:Entity e WITH i.idEntity = e.id
                            JOIN ArcanysEasyAppBundle:Vendor v WITH i.idVendor = v.id
                            WHERE i.dateadded >= '$getFirstMonth' AND i.dateadded <= '$getEndMonth'
                            AND i.status NOT IN (1, 11, 2, 22, 9)
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
							"
						);
		$rev_total 		= $query->getResult();
		$rev_total = $rev_total = (($rev_total[0][1] != null) ? (int) $rev_total[0][1] : null);
		
		$query			= $em->createQuery(
							"
							SELECT 
							  SUM(c.amount) 
							FROM
							  ArcanysEasyAppBundle:Revenueinter c 
							WHERE strtodate(c.dateadded, '%m-%d-%Y') >= '$getFirstMonth'
							  AND strtodate(c.dateadded, '%m-%d-%Y') <= '$getEndMonth'
							"
						);
		$cap_total 		= $query->getResult();
		$cap_total = $cap_total = (($cap_total[0][1] != null) ? (int) $cap_total[0][1] : null);

        $response = array( "success" => true, "total" => $total, "rev_total" => $rev_total, "cap_total" => $cap_total );
        return new JsonResponse($response);
    }

    public function getdatetodateexpenseAction()
    {
        $getFromMonth   = (isset($_POST['from'])) ? $_POST['from'] : '';
        $getToMonth     = (isset($_POST['to'])) ? $_POST['to'] : '';

        $FromMonth      = date('Y-m-d 00:00:00', strtotime($getFromMonth));
        $ToMonth        = date('Y-m-d 23:59:59', strtotime($getToMonth));

        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT i.invoiceId, i.dueDate, i.status, i.amount, i.dateadded, e.entityName, e.id, v.id, v.name
                            FROM ArcanysEasyAppBundle:Invoice i
                            JOIN ArcanysEasyAppBundle:Entity e WITH i.idEntity = e.id
                            JOIN ArcanysEasyAppBundle:Vendor v WITH i.idVendor = v.id
                            WHERE i.dateadded >= '$FromMonth' AND i.dateadded <= '$ToMonth'
                            AND i.status NOT IN (1, 11, 2, 22, 9)
                            GROUP BY i.invoiceId"
                        );
        $eEntity        = $query->getResult();

        $total = 0;
//        var_dump($eEntity);
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
							"
						);
		$rev_total 		= $query->getResult();
		$rev_total = $rev_total = (($rev_total[0][1] != null) ? (int) $rev_total[0][1] : null);
		
		$query			= $em->createQuery(
							"
							SELECT 
							  SUM(c.amount) 
							FROM
							  ArcanysEasyAppBundle:Revenueinter c 
							WHERE strtodate(c.dateadded, '%m-%d-%Y') >= '$FromMonth'
							  AND strtodate(c.dateadded, '%m-%d-%Y') <= '$ToMonth'
							"
						);
		$cap_total 		= $query->getResult();
		$cap_total = $cap_total = (($cap_total[0][1] != null) ? (int) $cap_total[0][1] : null);

        $response = array( "code" => 100, "success" => true, "total" => $total, "rev_total" => $rev_total, "cap_total" => $cap_total );
        return new JsonResponse($response);
    }
	
	public function downloadcsvAction(Request $request)
	{
		$val = $request->request->all();
		
		//print_r($val['header']); exit();
		
		$em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT
                                i.invoiceId, i.invoicenumber, i.status, i.description, i.status, i.dueDate, i.checkNo, i.amount, dateformat(i.dateadded, '%m-%d-%Y') as dateadded, i.dateupdated,
                                e.entityName, e.id, e.bankName, e.curBalance, e.bankAcct,
                                v.id, v.name
                            FROM ArcanysEasyAppBundle:Entity e
                            JOIN ArcanysEasyAppBundle:Invoice i WITH i.idEntity = e.id
                            JOIN ArcanysEasyAppBundle:Vendor v WITH i.idVendor = v.id
                            WHERE i.status NOT IN (1, 11, 2, 22, 9)
                            GROUP BY i.invoiceId
                            ORDER BY i.dateupdated DESC"
                        );
        $invoice        = $query->getArrayResult();
		
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
							ORDER BY r.dateadded DESC"
                        );
        $capital       	= $query->getArrayResult();
		
		$test = array_merge($revenue, $invoice, $capital);
		
		usort($test, function ($a, $b) {
			if($a['dateadded'] == $b['dateadded']){
				return 0;
			}
			return ($a['dateadded'] > $b['dateadded']) ? -1 : 1;
		});
		
		$filename = "export_".date("Y_m_d_His").".csv";
		
		$response = $this->render('ArcanysEasyAppBundle:Masterregistry:csv.html.twig', array('data' => $test, 'val' => $val['header'])); 
	 
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
