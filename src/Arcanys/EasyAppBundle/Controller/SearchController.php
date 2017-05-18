<?php

namespace Arcanys\EasyAppBundle\Controller;

use Arcanys\EasyAppBundle\Entity\Entity;
use Arcanys\EasyAppBundle\Entity\Entitychecknum;
use Arcanys\EasyAppBundle\Entity\Invoice;
use Arcanys\EasyAppBundle\Entity\InvoiceImages;
use Arcanys\EasyAppBundle\Entity\Invoicecomments;
use Arcanys\EasyAppBundle\Entity\Vendor;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    public function indexAction()
    {
        $request = $this->get('request');
        $data    = $request->get('q');
        $filter  = $request->get('filter');

        $queryFilter = $nSearch = "
             i.invoicenumber LIKE '%" . $data  . "%' OR
             i.checkNo LIKE '%" . $data  . "%' OR
             e.entityName LIKE '%" . $data  . "%' OR
             eb.bankName LIKE '%" . $data  . "%' OR
             eb.bankAcct LIKE '%" . $data  . "%' OR
             eb.routingBalance LIKE '%" . $data  . "%' OR
             u.firstname LIKE '%" . $data  . "%' OR
             u.lastname LIKE '%" . $data  . "%' OR
             u.email LIKE '%" . $data  . "%' OR
             v.name LIKE '%" . $data  . "%'
        ";

        $aSearch = "";
        if ($filter > 0) {
            $ctr = 0;

            for ($i = 1; $ctr < $filter; $i++) {
                $search = $request->get('search_' . $i);

                if ($search) {
                    switch($request->get('field_' . $i)) {
                        case 'All Fields': $aSearch .= str_replace("'%%'", "'%{$search}%'", $nSearch); break;
                        case 'Invoice No': $aSearch .= "i.invoicenumber LIKE '%" . $search  . "%' "; break;
                        case 'Entity': $aSearch .= "e.entityName LIKE '%" . $search  . "%' "; break;
                        case 'Bank': $aSearch .= "eb.bankName LIKE '%" . $search  . "%' "; break;
                        case 'Bank Account': $aSearch .= "eb.bankAcct LIKE '%" . $search  . "%' "; break;
                        case 'Routing Balance': $aSearch .= "eb.routingBalance LIKE '%" . $search  . "%' "; break;
                        case 'Vendor': $aSearch .= "v.name LIKE '%" . $search  . "%' "; break;
                    }

                    if ($ctr < ($filter -1))
                        $aSearch .= " OR ";

                    // The keys are inconsistent so match according
                    // to filter count
                    $ctr++;
                }
            }

            $queryFilter = $aSearch;
        }

        $queryFilter = " ( ". $queryFilter. " ) ";

        // SEARCH QUERY
        $em      = $this->getDoctrine()->getManager();
        $query   = $em->createQuery(
                     "SELECT
                        i.deletestatus, i.addedby, i.invoicedate, i.invoiceId, i.invoicenumber, i.status, i.entityready, i.printready,
                        i.description, i.dueDate, i.checkNo, i.amount, i.dateadded, i.dateupdated, i.outstandingbalance, i.remainingbalance,
                        e.entityName, e.id,
                        eb.bankName, eb.curBalance, eb.bankAcct,
                        v.id, v.name,
                        u.firstname, u.lastname, u.email
                      FROM ArcanysEasyAppBundle:Invoice i
                      LEFT JOIN ArcanysEasyAppBundle:Entity e
                        WITH i.idEntity = e.id
                      LEFT JOIN ArcanysEasyAppBundle:Entitybankinfo eb
                        WITH eb.entityNumId = e.id
                      LEFT JOIN ArcanysEasyAppBundle:Vendor v
                        WITH i.idVendor = v.id
                      LEFT JOIN ArcanysEasyAppBundle:User u
                        WITH i.managerApproval = u.id
                      WHERE
                        (i.status = 4 OR
                         i.status = 11 OR
                         i.status = 22 OR
                         i.status = 33 OR
                         i.status = 55 OR
                         i.status = 10) AND

                         i.entityready = 1 AND
                         i.printready = 1 AND
                         i.deletestatus = 1 AND

                         {$queryFilter}

                         ORDER BY i.dateupdated ASC"
                   );
        $search  = $query->getArrayResult();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $search, $this->get('request')->query->get('p', 1), 10,
            array('pageParameterName' => 'p')
        );

        return $this->render('ArcanysEasyAppBundle:Search:index.html.twig', array(
            'search'    => $pagination
        ));
    }
}
