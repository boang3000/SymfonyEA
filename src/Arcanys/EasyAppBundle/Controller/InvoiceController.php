<?php

namespace Arcanys\EasyAppBundle\Controller;

use Arcanys\EasyAppBundle\Entity\Entity;
use Arcanys\EasyAppBundle\Entity\Entitychecknum;
use Arcanys\EasyAppBundle\Entity\Invoice;
use Arcanys\EasyAppBundle\Entity\Invoiceebankinfo;
use Arcanys\EasyAppBundle\Entity\InvoiceRepository;
use Arcanys\EasyAppBundle\Entity\InvoiceImages;
use Arcanys\EasyAppBundle\Entity\Invoicecomments;
use Arcanys\EasyAppBundle\Entity\Vendor;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class InvoiceController extends Controller
{
    # status 0 = aprroved by manager    status 4 = aprroved all by admin
    # status 1 = for approval           status 11 = admin's for approval
    # status 2 = not approve            status 22 = admin's not approve
    # status 3 = manual                 status 33 = admin's manual
    # status 54 = partial payment       status 44 = created new invoice from partial payment
    # status 9 = DRAFT
    public function indexAction(Request $request)
    {
        // pending
        $user           = $this->get('security.context')->getToken()->getUser();
		$current_role   = $user->getRoles();
        $company_id     = $user->getCompany();

        $getuser        = $this->getDoctrine()
                               ->getRepository('ArcanysEasyAppBundle:User')
                               ->findBy( array('company' => $company_id) );
        $session        = $request->getSession();

        // store an attribute for reuse during a later user request
        $getuserid = array();
        foreach($getuser as $k => $v) {
            $getuserid[] = $v->getCompany();
        }
        $session->set('company', $getuserid);

        // in another controller for another request
        $company = $session->get('company');

		if($current_role[0] == 'ROLE_SUPER_ADMIN') {
			return $this->redirect($this->generateUrl('EA_superadmin_addaccount'));
		}

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
                            $aSearch .= "i.invoicenumber LIKE '%" . $search  . "%' OR
                                 i.checkNo LIKE '%" . $search  . "%' OR
                                 e.entityName LIKE '%" . $search  . "%' OR
                                 e.bankName LIKE '%" . $search  . "%' OR
                                 e.bankAcct LIKE '%" . $search  . "%' OR
                                 e.curBalance LIKE '%" . $search  . "%' OR
                                 v.name LIKE '%" . $search  . "%'";
                            break;
                        case 'Invoice No': $aSearch .= "i.invoicenumber LIKE '%" . $search  . "%' "; break;
                        case 'Entity': $aSearch .= "e.entityName LIKE '%" . $search  . "%' "; break;
                        case 'Bank': $aSearch .= "e.bankName LIKE '%" . $search  . "%' "; break;
                        case 'Bank Account': $aSearch .= "e.bankAcct LIKE '%" . $search  . "%' "; break;
                        case 'Routing Balance': $aSearch .= "e.curBalance LIKE '%" . $search  . "%' "; break;
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

        // for admin dashboard
        // approval
		$em             = $this->getDoctrine()->getManager();
        $q              = $em->createQuery(
                                "SELECT
                                    i.deletestatus, i.invoiceId, i.invoicedate, i.invoicenumber, i.description, i.status, i.remainingbalance,
                                    i.addedby, i.dueDate, i.checkNo, i.amount, i.dateadded, i.dateupdated, i.outstandingbalance, i.managerApproval,
                                    e.entityName, e.id, e.bankName, e.curBalance, e.bankAcct,
                                    v.id, v.name,
                                    u.firstname, u.lastname
                                FROM ArcanysEasyAppBundle:Entity e
                                LEFT JOIN ArcanysEasyAppBundle:Invoice i
                                    WITH i.idEntity = e.id
                                LEFT JOIN ArcanysEasyAppBundle:Vendor v
                                    WITH i.idVendor = v.id
                                LEFT JOIN ArcanysEasyAppBundle:User u
                                    WITH i.addedby = u.id
                                WHERE
                                    (i.status = 0 OR
                                     i.status = 33 OR
                                     i.status = 44 OR
                                     i.status = 5 OR
                                     i.managerApproval = 99999 AND i.status = 1 OR
                                     i.managerApproval = 99999 AND i.status = 3) AND
                                     i.deletestatus = 1 AND
                                     i.company = '" . $company[0] . "'
                                     " . $queryFilter . "
                                GROUP BY i.invoiceId
                                ORDER BY
                                    i.dateupdated DESC"
                            );
        $invoice        = $q->getResult();

        $page           = $this->get('knp_paginator');
        $adminslist     = $page->paginate(
                          $invoice, $this->get('request')->query->get('page', 1), 10
                        );

        // for manager dashboard
        // approval
        $manager        = $this->getDoctrine()->getManager();
        $mquery         = $manager->createQuery(
                                "SELECT
                                    i.deletestatus, i.invoiceId, i.invoicedate, i.invoicenumber, i.description, i.status, i.remainingbalance,
                                    i.addedby, i.dueDate, i.checkNo, i.amount, i.dateadded, i.dateupdated, i.outstandingbalance,
                                    e.entityName, e.id, e.bankName, e.curBalance, e.bankAcct,
                                    v.id, v.name
                                FROM ArcanysEasyAppBundle:Invoice i
                                LEFT JOIN ArcanysEasyAppBundle:Entity e
                                    WITH i.idEntity = e.id
                                LEFT JOIN ArcanysEasyAppBundle:Vendor v
                                    WITH i.idVendor = v.id
                                WHERE
                                    i.managerApproval = " . $user->getId() . " AND
                                    i.deletestatus = 1 AND
                                    (i.status = 1 OR
                                     i.status = 3 OR
                                     i.status = 54 OR
                                     i.managerApproval = 99999 AND i.status = 4 OR
                                     i.managerApproval = 99999 AND i.status = 3) AND
                                     i.company = '" . $company[0] . "'
                                     " . $queryFilter . "
                                GROUP BY i.invoiceId
                                ORDER BY
                                    i.dateupdated DESC"
                            );
        $managerinvoice = $mquery->getResult();
        $mpaginator     = $this->get('knp_paginator');
        $mpagination    = $mpaginator->paginate(
                          $managerinvoice, $this->get('request')->query->get('approval', 1), 10,
                            array('pageParameterName' => 'approval')
                        );

        // for accountant dashboard
        // pending
        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                            "SELECT
                                i.deletestatus, i.addedby, i.invoicedate, i.invoiceId, i.invoicenumber, i.assigned,
                                i.status, i.entityready, i.printready, i.description, i.dueDate, i.checkNo,
                                i.amount, i.dateadded, i.dateupdated, i.outstandingbalance, i.remainingbalance,
                                e.entityName, e.id, e.bankName, e.curBalance, e.bankAcct,
                                v.id, v.name,
                                u.firstname, u.lastname, u.id,
                                c.comments, COUNT(c.invoicecommentId) AS count_id
                            FROM ArcanysEasyAppBundle:Invoice i
                            LEFT JOIN ArcanysEasyAppBundle:Entity e
                                WITH i.idEntity = e.id
                            LEFT JOIN ArcanysEasyAppBundle:Vendor v
                                WITH i.idVendor = v.id
                            LEFT JOIN ArcanysEasyAppBundle:Invoicecomments c
                                WITH c.invoicecommentId = i.invoiceId
                            LEFT JOIN ArcanysEasyAppBundle:User u
                                WITH u.id = i.managerApproval OR u.id = i.assigned
                            WHERE
                                i.deletestatus = 1 AND
                                (i.status = 0 OR
                                 i.status = 1 OR
                                 i.status = 2 OR
                                 i.status = 3 OR
                                 i.status = 5 OR
                                 i.status = 22 OR
                                 i.status = 33 OR
                                 i.status = 44 OR
                                 i.status = 55 OR
                                 i.status = 10) AND
                                 i.addedby = '" . $user->getId() . "' AND
                                 i.company = '" . $company[0] . "'
                                " . $queryFilter . "
                            GROUP BY i.invoiceId
                            ORDER BY
                                i.dateupdated DESC"
                        );
        $invoice        = $query->getResult();
        $paginator      = $this->get('knp_paginator');
        $pagination     = $paginator->paginate(
                          $invoice, $this->get('request')->query->get('pending', 1), 10,
                            array('pageParameterName' => 'pending')
                        );

        // approved
        $em2            = $this->getDoctrine()->getManager();
        $query2         = $em2->createQuery(
                            "SELECT
                                i.deletestatus, i.invoiceId, i.invoicenumber, i.invoicedate, i.addedby, i.dateupdated, i.entityready, i.remainingbalance,
                                i.description, i.status, i.dueDate, i.checkNo, i.amount, i.dateadded, i.outstandingbalance, i.printready,
                                e.entityName, e.id, e.bankName, e.curBalance, e.bankAcct,
                                v.id, v.name
                            FROM ArcanysEasyAppBundle:Entity e
                            LEFT JOIN ArcanysEasyAppBundle:Invoice i
                                WITH i.idEntity = e.id
                            LEFT JOIN ArcanysEasyAppBundle:Vendor v
                                WITH i.idVendor = v.id
                            WHERE
                                (i.status = 4) AND i.deletestatus = 1 AND
                                 i.addedby = '" . $user->getId() . "' AND
                                 i.company = '" . $company[0] . "'
                                " . $queryFilter . "
                            GROUP BY i.invoiceId
                            ORDER BY
                                i.dateupdated DESC"
                        );
        $invoice2       = $query2->getResult();
        $paginator2     = $this->get('knp_paginator');
        $approved       = $paginator2->paginate(
                          $invoice2, $this->get('request')->query->get('approved', 1), 10,
                            array('pageParameterName' => 'approved')
                        );

        // drafts
        $em3             = $this->getDoctrine()->getManager();
        $query3          = $em3->createQuery(
                            "SELECT
                                i.deletestatus, i.addedby, i.invoicedate, i.invoiceId, i.invoicenumber, i.status,
                                i.description, i.dueDate, i.checkNo, i.amount, i.dateadded, i.dateupdated,
                                e.entityName, e.id, e.bankName, e.curBalance, e.bankAcct,
                                v.id, v.name,
                                u.firstname, u.lastname,
                                c.comments
                            FROM ArcanysEasyAppBundle:Invoice i
                            LEFT JOIN ArcanysEasyAppBundle:Entity e
                                WITH i.idEntity = e.id
                            LEFT JOIN ArcanysEasyAppBundle:Vendor v
                                WITH i.idVendor = v.id
                            LEFT JOIN ArcanysEasyAppBundle:Invoicecomments c
                                WITH c.invoicecommentId = i.invoiceId
                            JOIN ArcanysEasyAppBundle:User u
                                WITH i.addedby = u.id
                            WHERE
                                i.deletestatus = 1 AND
                                i.addedby = '" . $user->getId() . "' AND
                                i.status = 9 AND
                                i.company = '" . $company[0] . "'
                                " . $queryFilter . "
                            GROUP BY i.invoiceId
                            ORDER BY
                                i.dateupdated DESC"
                        );
        $invoiced       = $query3->getResult();

        $paginator3     = $this->get('knp_paginator');
        $pagination3    = $paginator3->paginate(
                          $invoiced, $this->get('request')->query->get('draft', 1), 10,
                            array('pageParameterName' => 'draft')
                        );

        // GET ALL MANAGER AND THEIR INVOICE DATA
        $managerrepo    = $this->getDoctrine()->getManager()
                               ->createQuery(
                                    "SELECT u
                                     FROM ArcanysEasyAppBundle:User u
                                     WHERE u.roles LIKE '%ROLE_MANAGER%' AND u.company = '" . $company[0] . "'
                                     "
                               );
        $manager        = $managerrepo->getResult();

        return $this->render('ArcanysEasyAppBundle:Invoice:index.html.twig', array(
            'invoice'    => $invoice,
            'pagination' => $pagination,
            'approved'   => $approved,
            'draft'      => $pagination3,
            'adminslist' => $adminslist,
            'managelist' => $mpagination,
            'manager'    => $manager
        ));
    }

    // DISPLAY THE DATA OF THE MANAGER
    public function managerdataAction()
    {
        $request       = $this->get('request');
        $session       = $request->getSession();
        $company       = $session->get('company');
        $id            = (isset($_POST['id'])) ? $_POST['id'] : '';
        $managerrepo   = $this->getDoctrine()->getManager();
        $query         = $managerrepo->createQuery(
                                "
                                SELECT
                                    i.deletestatus, i.invoiceId, i.invoicedate, i.invoicenumber, i.description, i.status, i.remainingbalance,
                                    i.addedby, i.dueDate, i.checkNo, i.amount, i.dateadded, i.dateupdated, i.outstandingbalance,
                                    e.entityName, e.id, e.bankName, e.curBalance, e.bankAcct,
                                    v.id, v.name
                                FROM ArcanysEasyAppBundle:Invoice i
                                LEFT JOIN ArcanysEasyAppBundle:Entity e
                                    WITH i.idEntity = e.id
                                LEFT JOIN ArcanysEasyAppBundle:Vendor v
                                    WITH i.idVendor = v.id
                                WHERE
                                    i.managerApproval = " . $id . " AND
                                    i.deletestatus = 1 AND
                                    (i.status = 1 OR
                                     i.status = 3 OR
                                     i.status = 54 OR
                                     i.managerApproval = 99999 AND i.status = 4 OR
                                     i.managerApproval = 99999 AND i.status = 3) AND
                                     i.company = '" . $company[0] . "'
                                GROUP BY i.invoiceId
                                ORDER BY
                                    i.dateupdated DESC"
                            );
        $getManager      = $query->getResult();

        return $this->render('ArcanysEasyAppBundle:Invoice:managerinvoice.html.twig', array(
            'manager' => $getManager,
        ));
    }

    // DISPLAY LIST OF COMMENTS
    public function invoicecommentsAction($id)
    {
        $em              = $this->getDoctrine()->getManager();
        $query           = $em->createQuery(
                                "SELECT
                                    u.firstname, u.lastname, u.id, u.roles,
                                    c.comments, c.invoicecommentId, c.dateadded
                                FROM ArcanysEasyAppBundle:Invoicecomments c
                                LEFT JOIN ArcanysEasyAppBundle:User u
                                    WITH c.addedby = u.id
                                WHERE
                                    c.invoicecommentId = '" . $id .  "'
                                GROUP BY c.id
                                ORDER BY c.dateadded DESC"
                            );
        $comment        = $query->getResult();

        return $this->render('ArcanysEasyAppBundle:Invoice:invoicecomments.html.twig', array(
            'comment'    => $comment,
        ));
    }

    public function detailAction($id)
    {
        $request        = $this->get('request');
        $session        = $request->getSession();
        $company        = $session->get('company');
        $invoicerepo    = $this->getDoctrine()->getManager();
        $invoice        = $invoicerepo->getRepository('ArcanysEasyAppBundle:Invoice')
                                      ->find($id);

        // GET INVOICE BANK INFO
        $invoiceinfo    = $invoicerepo->getRepository('ArcanysEasyAppBundle:Invoiceebankinfo')
                                      ->findBy( array( 'invoiceinfoId' => $invoice->getInvoiceId() ) );

        if ( empty($invoiceinfo) ) {
            $invoiceBankinfoId = '';
        } else {
            $invoiceBankinfoId = $invoiceinfo[0]->getEntitybankinfoId();
        }

        $getinvoiceinfo = $invoicerepo->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                                      ->findBy( array( 'entitybankId' => $invoiceBankinfoId ) );

        // GET INVOICE IMAGES DETAILS
        $invoiceimgrepo = $this->getDoctrine()->getManager();
        $invoiceimg     = $invoiceimgrepo->getRepository('ArcanysEasyAppBundle:InvoiceImages')
                                         ->findBy( array('upltoken' => $invoice->getToken()) );

        // GET VENDOR DETAILS
        $vendorrepo     = $this->getDoctrine()->getManager();
        $vendor         = $vendorrepo->getRepository('ArcanysEasyAppBundle:Vendor')
                                     ->find($invoice->getIdVendor());

        // GET ENTITY DETAILS
        $entityrepo     = $this->getDoctrine()->getManager();
        $entity         = $entityrepo->getRepository('ArcanysEasyAppBundle:Entity')
                                     ->find($invoice->getIdEntity());

        $entitybankinfo = $entityrepo->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                                     ->findBy( array( 'entityNumId' => $entity->getId() ) );

        $entity_curbal  = $this->get('entity.values.handler')->getCurrentBalanceById($invoice->getIdEntity());

        // GET ALL USER'S EMAIl
        $userrepo       = $this->getDoctrine()->getManager();
        $email          = $userrepo->getRepository('ArcanysEasyAppBundle:User')->findAll();

        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getCharts      = $repository->findBy( array('id' => $invoice->getChartOfAccounts()) );

        $em2            = $this->getDoctrine()->getManager();
        $query2         = $em2->createQuery(
                            "SELECT
                                i.invoiceId, ic.comments, ic.addedby, ic.invoicecommentId, ic.dateadded,
                                u.firstname, u.roles
                            FROM ArcanysEasyAppBundle:Invoicecomments ic
                            LEFT JOIN ArcanysEasyAppBundle:User u
                                WITH ic.addedby = u.id
                            LEFT JOIN ArcanysEasyAppBundle:Invoice i
                                WITH ic.invoicecommentId = i.invoiceId
                            WHERE i.invoiceId = '" . $invoice->getInvoiceId() . "'
                            ORDER BY ic.dateadded DESC"
                        );
        $comment        = $query2->getResult();
        $repochecknum   = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entitychecknum');

        // GET SPECIFIC USER DETAILS
        $user           = $userrepo->getRepository('ArcanysEasyAppBundle:User')
                                   ->findBy( array('id' => $invoice->getManagerApproval()) );
        if ( $invoice->getManagerApproval() == 99999 ) {
            if ( empty($user) ) {
                $user = 99999;
            }
            $userimage = 99999;
        } else {
            if ( empty($user) ) {
                $userimage = '';
            }
            else {
                $userimage = $userrepo->getRepository('ArcanysEasyAppBundle:Userimage')
                                      ->findBy( array('token' => $user[0]->getToken()) );
            }
        }

        // FOR MANAGER'S SIGNATURE
        $getuseradmin   = $this->getDoctrine()->getManager()
                               ->createQuery(
                                   "SELECT u
                                    FROM ArcanysEasyAppBundle:User u
                                    WHERE u.roles LIKE '%ROLE_ADMIN%' AND u.company = '" . $company[0] . "'"
                               );
        $getadmin       = $getuseradmin->getResult();

        // FOR ADMIN'S SIGNATURE
        $userrepo3      = $this->getDoctrine()->getManager();
        $adminimage     = $userrepo3->getRepository('ArcanysEasyAppBundle:Userimage')
                                    ->findBy( array('token' => $getadmin[0]->getToken()) );

        return $this->render('ArcanysEasyAppBundle:Invoice:detail.html.twig', array(
            'invoice'     => $invoice,
            'invoiceinfo' => $getinvoiceinfo,
            'invoiceimg'  => $invoiceimg,
            'fimage'      => reset($invoiceimg),
            'vendor'      => $vendor,
            'entity'      => $entity,
            'bankinfo'    => $entitybankinfo,
            'user'        => $user,
            'getadmin'    => $getadmin,
            'adminsign'   => $adminimage,
            'email'       => $email,
            'comment'     => $comment,
            'charts'      => $getCharts,
            'userimage'   => $user,
            'curbal'      => $entity_curbal,
        ));
    }

    public function updateAction($id, Request $request)
    {
        $session        = $request->getSession();
        $company        = $session->get('company');
        $invoicerepo    = $this->getDoctrine()->getManager();
        $invoice        = $invoicerepo->getRepository('ArcanysEasyAppBundle:Invoice')->find($id);

        // GET INVOICE IMAGES DETAILS
        $repository     = $this->getDoctrine()->getManager();
        $invoiceimg     = $repository->getRepository('ArcanysEasyAppBundle:InvoiceImages')
                                         ->findBy( array('idInvoice' => $invoice->getInvoiceId()) );

        $vendor         = $repository->getRepository('ArcanysEasyAppBundle:Vendor')->find($invoice->getIdVendor());
        $entityrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $query1         = $entityrepo->createQueryBuilder('e')
                                     ->orderBy('e.dateadded', 'DESC')
                                     ->getQuery();
        $listvendor     = $query1->getResult();

        $entity         = $repository->getRepository('ArcanysEasyAppBundle:Entity')->find($invoice->getIdEntity());
        $entityrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $query2         = $entityrepo->createQueryBuilder('e')
                                     ->orderBy('e.dateadded', 'DESC')
                                     ->getQuery();
        $listentity     = $query2->getResult();

        $user           = $repository->getRepository('ArcanysEasyAppBundle:User')->find($invoice->getManagerApproval());
        $entityrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:User');
        $query3         = $entityrepo->createQueryBuilder('e')
                                     ->orderBy('e.dateadded', 'DESC')
                                     ->getQuery();
        $listuser       = $query3->getResult();

        // GET ADMIN DETAILS
        $getadmin       = $repository->getRepository('ArcanysEasyAppBundle:User')->find('1');

        // IF POST UPDATED
        if ( $request->getMethod() == 'POST' ) {
            $amount             = $request->get('amount');
            $invoice->setAmount(str_replace(',', '' , $amount));
            $invoicerepo->flush();

            return $this->redirect($this->generateUrl('EA_information_invoice', array('id' => $id)));
        }

        if ( empty($user) or empty($getadmin) ) {
            $token      = '';
            $admintoken = '';
        } else {
            $token      = $user->getToken();
            $admintoken = $getadmin->getToken();
        }

        // FOR MANAGER'S SIGNATURE
        $userimage      = $repository->getRepository('ArcanysEasyAppBundle:Userimage')->findBy( array('token' => $token) );

        // GET SPECIFIC USER DETAILS
        $user           = $repository->getRepository('ArcanysEasyAppBundle:User')
                                     ->findBy( array('id' => $invoice->getManagerApproval()) );

        $repochecknum   = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entitychecknum');
        $chkChecknum    = $repochecknum->findBy( array('chkInvoiceid' => $id) ); // check if data exist

        $repocharts     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getChartsdata  = $repocharts->findBy( array( 'company' => $company ) );

        // FOR ADMIN'S SIGNATURE
        $adminimage     = $repository->getRepository('ArcanysEasyAppBundle:Userimage')
                                     ->findBy( array('token' => $admintoken) );
        $query2         = $repository->createQuery(
                                "SELECT
                                    i.invoiceId, ic.comments, ic.addedby, ic.invoicecommentId, ic.dateadded,
                                    u.firstname, u.roles
                                FROM ArcanysEasyAppBundle:Invoicecomments ic
                                LEFT JOIN ArcanysEasyAppBundle:User u
                                    WITH ic.addedby = u.id
                                LEFT JOIN ArcanysEasyAppBundle:Invoice i
                                    WITH ic.invoicecommentId = i.invoiceId
                                WHERE i.invoiceId = '" . $invoice->getInvoiceId() . "'
                                ORDER BY ic.dateadded DESC
                                "
                            );
        $comment        = $query2->getResult();

        return $this->render('ArcanysEasyAppBundle:Invoice:edit.html.twig', array(
            'invoice'   => $invoice,
            'invoiceimg'=> $invoiceimg,
            'fimage'    => reset($invoiceimg),
            'vendor'    => $vendor,
            'listven'   => $listvendor,
            'listent'   => $listentity,
            'entity'    => $entity,
            'user'      => $user,
            'userimage' => $userimage,
            'getadmin'  => $adminimage,
            'charts'    => $getChartsdata,
            'comment'   => $comment,
            'listuser'  => $listuser,
            'checknum'  => $chkChecknum
        ));
    }

    // SUBMIT ALL AT ONCE
    public function submitmultiAction(Request $request)
    {
        if ( $request->getMethod() == 'POST' ) {
            $formtoken          = $request->get('formtoken');

            // update invoice and sent to pending
            $repository         = $this->getDoctrine()->getManager();
            $getInvoice         = $repository->getRepository('ArcanysEasyAppBundle:Invoice')
                                             ->findBy( array('formtoken' => $formtoken) );

            foreach( $getInvoice as $Invoice ) {
                $Invoice->setStatus('1');
                if ( $Invoice->getStatus() == 9 ) {
                    $Invoice->setStatus('1');
                }
                $repository->persist($Invoice);
//                var_dump($Invoice->getInvoiceId());
            }
            $repository->flush();
        }

        $response = array( "success" => true, 'token' => $formtoken );
        return new JsonResponse($response);
    }

    // CREATE MULTI INVOICES
    public function createmultiAction($num = NULL, Request $request)
    {
        // generate check number
        $today              = date('mdYHi');
        $startDate          = date('mdYHi', strtotime('03-14-2012 09:06:00'));
        $range              = $startDate - $today;
        $rand               = rand(0, $range);
        $generatecheck      = md5($rand . ($startDate + $rand));
        $user               = $this->get('security.context')->getToken()->getUser();
        $session            = $request->getSession();
        $company            = $session->get('company');

        if ( $request->getMethod() == 'POST' ) {
            $entityname         = $request->get('entity');
            $datedue            = $request->get('datedue');
            $vendorname         = $request->get('vendor');
            $amount             = $request->get('amount');
            $managerapproval    = $request->get('managerapproval');
            $invoicenum         = $request->get('invoiceamount');
            $invoicedate        = $request->get('invoicedate');
            $description        = $request->get('description');
            $chartofaccounts    = $request->get('chartofaccounts');
            $comments           = $request->get('comments');
            $token              = $request->get('token');
            $formtoken          = $request->get('formtoken');
            $pagenumber         = $request->get('pagenumber');

            $repoentity         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
            $getEntity          = $repoentity->findOneBy( array('id' => $entityname) );

            $searchexisiting    = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Invoice');
            $getExisting        = $searchexisiting->findBy( array('token' => $token) );

            $invoicerepo        = $this->getDoctrine()->getManager();
            $getAdmin           = $invoicerepo
                                    ->createQuery(
                                        "SELECT u
                                         FROM ArcanysEasyAppBundle:User u
                                         WHERE u.company = '" . $user->getCompany() . "' AND u.roles LIKE '%ROLE_ADMIN%'"
                                    )
                                    ->getResult();

            // get starting checknumber
            if ( empty($getEntity) ) {
                $getStartbalaance = '';
            } else {
                $getStartbalaance = $getEntity->getStartBalance();
            }

            // get pagenumber
            if ( empty($pagenumber) ) {
                $pagenumber = '1';
            } else {
                $pagenumber = $request->get('pagenumber');
            }

            $em = $this->getDoctrine()->getManager();

            if ( empty($getExisting) ) { // IF DATA NOT EXIST, INSERT DATA
                $invoice = new Invoice();
                $invoice->setIdEntity($entityname);
                $invoice->setIdVendor($vendorname);
                $invoice->setCheckNo('0');
                $invoice->setDueDate($datedue);
                $invoice->setAmount($amount);
                $invoice->setStatus('9');
                $invoice->setManagerApproval($managerapproval);
                if ( $managerapproval == '99999' ) {
                    $invoice->setAssigned($getAdmin[0]->getId());
                } else {
                    $invoice->setAssigned($managerapproval);
                }
                $invoice->setAddedby($user->getId());
                $invoice->setInvoicedate($invoicedate);
                $invoice->setInvoicenumber($invoicenum);
                $invoice->setDescription($description);
                $invoice->setDeletestatus('1');
                $invoice->setReadstatus('1');
                $invoice->setStatusdraft('1');
                $invoice->setChartOfAccounts($chartofaccounts);
                $invoice->setToken($token);
                $invoice->setFormtoken($formtoken);
                $invoice->setPagenumber($pagenumber);
                $invoice->setCompany($user->getCompany());
                $em->persist($invoice);

                $commentsdrafts = new Invoicecomments();
                $commentsdrafts->setInvoicecommentId('0');
                $commentsdrafts->setComments($comments);
                $commentsdrafts->setAddedby($user->getId());
                $commentsdrafts->setStatus('0');
                $commentsdrafts->setToken($token);

                $em->persist($commentsdrafts);
            }
            else { // IF EXIST, UPDATE DATA
                $updateinvoice  = $em->getRepository('ArcanysEasyAppBundle:Invoice')
                                     ->find($getExisting[0]->getInvoiceId());

                $updateinvoice->setIdEntity($entityname);
                $updateinvoice->setIdVendor($vendorname);
                $updateinvoice->setCheckNo('0');
                $updateinvoice->setDueDate($datedue);
                $updateinvoice->setAmount($amount);
                $updateinvoice->setStatus('9');
                $updateinvoice->setManagerApproval($managerapproval);
                if ( $managerapproval == '99999' ) {
                    $updateinvoice->setAssigned($getAdmin[0]->getId());
                } else {
                    $updateinvoice->setAssigned($managerapproval);
                }
                $updateinvoice->setAddedby($user->getId());
                $updateinvoice->setInvoicedate($invoicedate);
                $updateinvoice->setInvoicenumber($invoicenum);
                $updateinvoice->setDescription($description);
                $updateinvoice->setDeletestatus('1');
                $updateinvoice->setReadstatus('1');
                $updateinvoice->setChartOfAccounts($chartofaccounts);
                $updateinvoice->setToken($token);
                $updateinvoice->setFormtoken($formtoken);
                $updateinvoice->setPagenumber($pagenumber);

                $getinvoicecomments = $em->getRepository('ArcanysEasyAppBundle:Invoicecomments')
                                         ->findOneBy( array('token' => $getExisting[0]->getToken()) );

                if ( !empty($getinvoicecomments) ) {
                    $getinvoicecomments->setInvoicecommentId($getExisting[0]->getInvoiceId());
                    $getinvoicecomments->setComments($comments);
                    $getinvoicecomments->setAddedby($user->getId());
                    $getinvoicecomments->setStatus('1');
                    $getinvoicecomments->setToken($token);
                } else {
                    $commentsdrafts = new Invoicecomments();
                    $commentsdrafts->setInvoicecommentId($getExisting[0]->getInvoiceId());
                    $commentsdrafts->setComments($comments);
                    $commentsdrafts->setAddedby($user->getId());
                    $commentsdrafts->setStatus('1');
                    $commentsdrafts->setToken($token);

                    $em->persist($commentsdrafts);
                }
            }
            $em->flush();
            $em->clear();
        } // END POST

        // GET LIST OF VENDOR
        $vendorrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $query          = $vendorrepo->createQueryBuilder('v')
                                     ->where('v.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('v.dateadded', 'DESC')
                                     ->getQuery();
        $vendor         = $query->getResult();

        // GET LIST OF ENTITY
        $entityrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $query2         = $entityrepo->createQueryBuilder('e')
                                     ->where('e.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('e.dateadded', 'DESC')
                                     ->getQuery();
        $entity         = $query2->getResult();

        // GET LIST OF USERS
        $query3         = $this->getDoctrine()->getManager()
                               ->createQuery(
                                   "SELECT u
                                    FROM ArcanysEasyAppBundle:User u
                                    WHERE u.roles LIKE '%ROLE_MANAGER%' AND u.company = '" . $company[0] . "'");
        $user           = $query3->getResult();

        // GET LIST OF CHARTS OF ACCOUNTS
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getChartsdata  = $repository->findBy( array( 'company' => $company ) );

        return $this->render('ArcanysEasyAppBundle:Invoice:multipleinvoice.html.twig', array(
            'vendor'    => $vendor,
            'entity'    => $entity,
            'user'      => $user,
            'charts'    => $getChartsdata,
            'checknum'  => $generatecheck
        ));
    }

    // CREATE PARTIAL PAYMENT INVOICE
    public function createpartialAction(Request $request)
    {
        // generate check number
        $today              = date('mdYHi');
        $startDate          = date('mdYHi', strtotime('03-14-2012 09:06:00'));
        $range              = $startDate - $today;
        $rand               = rand(0, $range);
        $generatecheck      = md5($rand . ($startDate + $rand));
        $user               = $this->get('security.context')->getToken()->getUser();
        $session            = $request->getSession();
        $company            = $session->get('company');

        if ( $request->getMethod() == 'POST' ) {
            $entityname         = $request->get('entity');
            $datedue            = $request->get('datedue');
            $checknumber        = $request->get('checknum');
            $vendorname         = $request->get('vendor');
            $amount             = $request->get('amount');
            $managerapproval    = $request->get('managerapproval');
            $invoicenum         = $request->get('invoiceamount');
            $invoicedate        = $request->get('invoicedate');
            $description        = $request->get('description');
            $chartofaccounts    = $request->get('chartofaccounts');
            $comments           = $request->get('comments');
            $token              = $request->get('token');

            $invoicerepo        = $this->getDoctrine()->getManager();
            $getAdmin           = $invoicerepo
                                    ->createQuery(
                                        "SELECT u
                                         FROM ArcanysEasyAppBundle:User u
                                         WHERE u.company = '" . $user->getCompany() . "' AND u.roles LIKE '%ROLE_ADMIN%'"
                                    )
                                    ->getResult();

            $invoice = new Invoice();
            $invoice->setIdEntity($entityname);
            $invoice->setIdVendor($vendorname);
            $invoice->setCheckNo($checknumber);
            $invoice->setDueDate($datedue);
            $invoice->setAmount($amount);
            $invoice->setStatus('3');
            $invoice->setManagerApproval($managerapproval);
            if ( $managerapproval == '99999' ) {
                $invoice->setAssigned($getAdmin[0]->getId());
            } else {
                $invoice->setAssigned($managerapproval);
            }
            $invoice->setAddedby($user->getId());
            $invoice->setInvoicedate($invoicedate);
            $invoice->setInvoicenumber($invoicenum);
            $invoice->setDescription($description);
            $invoice->setDeletestatus('1');
            $invoice->setReadstatus('1');
            $invoice->setChartOfAccounts($chartofaccounts);
            $invoice->setToken($token);
            $invoice->setCompany($user->getCompany());

            $em = $this->getDoctrine()->getManager();
            $em->persist($invoice);
            $em->flush();

            $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Invoice');
            $getInvoice         = $repository->findOneBy( array('token' => $token) );

            $getcomments = new Invoicecomments();
            $getcomments->setComments($comments);
            $getcomments->setAddedby($user->getId());
            $getcomments->setStatus('1');
            $getcomments->setInvoicecommentId($getInvoice->getInvoiceId());

            $commentsrepo = $this->getDoctrine()->getManager();
            $commentsrepo->persist($getcomments);
            $commentsrepo->flush();
        }

        // GET LIST OF VENDOR
        $vendorrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $query          = $vendorrepo->createQueryBuilder('v')
                                     ->where('v.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('v.dateadded', 'DESC')
                                     ->getQuery();
        $vendor         = $query->getResult();

        // GET LIST OF ENTITY
        $entityrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $query2         = $entityrepo->createQueryBuilder('e')
                                     ->where('e.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('e.dateadded', 'DESC')
                                     ->getQuery();
        $entity         = $query2->getResult();

        // GET LIST OF USERS
        $query3         = $this->getDoctrine()->getManager()
                               ->createQuery(
                                   "SELECT u
                                    FROM ArcanysEasyAppBundle:User u
                                    WHERE u.roles LIKE '%ROLE_MANAGER%' AND u.company = '" . $company[0] . "'");
        $user           = $query3->getResult();

        // GET LIST OF CHARTS OF ACCOUNTS
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getChartsdata  = $repository->findBy( array( 'company' => $company ) );

        return $this->render('ArcanysEasyAppBundle:Invoice:createpartial.html.twig', array(
            'vendor'    => $vendor,
            'entity'    => $entity,
            'user'      => $user,
            'charts'    => $getChartsdata,
            'checknum'  => $generatecheck
        ));
    }

    // CREATE SINGLE INVOICE
    public function createAction(Request $request)
    {
        // generate check number
        $today              = date('mdYHi');
        $startDate          = date('mdYHi', strtotime('03-14-2012 09:06:00'));
        $range              = $startDate - $today;
        $rand               = rand(0, $range);
        $generatecheck      = md5($rand . ($startDate + $rand));
        $user               = $this->get('security.context')->getToken()->getUser();
        $session            = $request->getSession();
        $company            = $session->get('company');

        // IF POST
        if ( $request->getMethod() == 'POST' ) {
            $entityname         = $request->get('entity');
            $datedue            = $request->get('datedue');
            $vendorname         = $request->get('vendor');
            $amount             = $request->get('amount');
            $managerapproval    = $request->get('managerapproval');
            $invoicenum         = $request->get('invoiceamount');
            $invoicedate        = $request->get('invoicedate');
            $description        = $request->get('description');
            $chartofaccounts    = $request->get('chartofaccounts');
            $comments           = $request->get('comments');
            $token              = $request->get('token');

            $invoicerepo        = $this->getDoctrine()->getManager();
            $getAdmin           = $invoicerepo
                                    ->createQuery(
                                        "SELECT u
                                         FROM ArcanysEasyAppBundle:User u
                                         WHERE u.company = '" . $user->getCompany() . "' AND u.roles LIKE '%ROLE_ADMIN%'"
                                    )
                                    ->getResult();

            $invoice = new Invoice();
            $invoice->setIdEntity($entityname);
            $invoice->setIdVendor($vendorname);
            $invoice->setCheckNo('0');
            $invoice->setDueDate($datedue);
            $invoice->setAmount($amount);
            $invoice->setStatus('1');
            $invoice->setManagerApproval($managerapproval);
            if ( $managerapproval == '99999' ) {
                $invoice->setAssigned($getAdmin[0]->getId());
            } else {
                $invoice->setAssigned($managerapproval);
            }
            $invoice->setAddedby($user->getId());
            $invoice->setInvoicedate($invoicedate);
            $invoice->setInvoicenumber($invoicenum);
            $invoice->setDescription($description);
            $invoice->setDeletestatus('1');
            $invoice->setReadstatus('1');
            $invoice->setChartOfAccounts($chartofaccounts);
            $invoice->setToken($token);
            $invoice->setCompany($user->getCompany());

            $em = $this->getDoctrine()->getManager();
            $em->persist($invoice);
            $em->flush();

            $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Invoice');
            $getInvoice         = $repository->findOneBy( array('token' => $token) );

            $getcomments = new Invoicecomments();
            $getcomments->setComments($comments);
            $getcomments->setAddedby($user->getId());
            $getcomments->setStatus('1');
            $getcomments->setInvoicecommentId($getInvoice->getInvoiceId());

            $commentsrepo = $this->getDoctrine()->getManager();
            $commentsrepo->persist($getcomments);
            $commentsrepo->flush();
        }

        // GET LIST OF VENDOR
        $vendorrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $query          = $vendorrepo->createQueryBuilder('v')
                                     ->where('v.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('v.dateadded', 'DESC')
                                     ->getQuery();
        $vendor         = $query->getResult();

        // GET LIST OF ENTITY
        $entityrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $query2         = $entityrepo->createQueryBuilder('e')
                                     ->where('e.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('e.dateadded', 'DESC')
                                     ->getQuery();
        $entity         = $query2->getResult();

        // GET LIST OF USERS
        $query3         = $this->getDoctrine()->getManager()
                                ->createQuery(
                                    "SELECT u
                                     FROM ArcanysEasyAppBundle:User u
                                     WHERE u.roles LIKE '%ROLE_MANAGER%' AND u.company = '" . $company[0] . "'");
        $user           = $query3->getResult();

        // GET LIST OF CHARTS OF ACCOUNTS
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getChartsdata  = $repository->findBy( array( 'company' => $company ) );

        return $this->render('ArcanysEasyAppBundle:Invoice:create.html.twig', array(
            'vendor'    => $vendor,
            'entity'    => $entity,
            'user'      => $user,
            'charts'    => $getChartsdata,
            'checknum'  => $generatecheck
        ));
    }

    public function updatecheckAction($id, Request $request)
    {
        $session        = $request->getSession();
        $company        = $session->get('company');
        // get value by id
        $invoicerepo    = $this->getDoctrine()->getManager();
        $invoice        = $invoicerepo->getRepository('ArcanysEasyAppBundle:Invoice')->find($id);
        $user           = $this->get('security.context')->getToken()->getUser();

        if ( $request->getMethod() == 'POST' ) {
            $entityname         = $request->get('entity');
            $datedue            = $request->get('datedue');
            $vendorname         = $request->get('vendor');
            $amount             = str_replace(array(',', '.00'), '', $request->get('amount'));
            $managerapproval    = $request->get('managerapproval');
            $invoicenum         = $request->get('invoiceamount');
            $checknum           = $request->get('checknum');
            $invoicedate        = $request->get('invoicedate');
            $description        = $request->get('description');
            $chartofaccounts    = $request->get('chartofaccounts');
            $comments           = $request->get('comments');
            $token              = $request->get('token');

            $repoentity         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
            $getEntz            = $repoentity->findOneBy( array('id' => $entityname) );

            $repoInvoice        = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Invoice');
            $getInvoice         = $repoInvoice->findBy( array('idVendor' => $vendorname, 'amount' => $amount) );

            $getAdmin           = $invoicerepo
                                    ->createQuery(
                                        "SELECT u
                                         FROM ArcanysEasyAppBundle:User u
                                         WHERE u.company = '" . $user->getCompany() . "' AND u.roles LIKE '%ROLE_ADMIN%'"
                                    )
                                    ->getResult();

            $invoice->setIdEntity($entityname);
            $invoice->setIdVendor($vendorname);
            $invoice->setDueDate($datedue);
            $invoice->setAmount($amount);
            $invoice->setCheckNo($checknum);
            $invoice->setManagerApproval($managerapproval);
            if ( $managerapproval == '99999' ) {
                $invoice->setAssigned($getAdmin[0]->getId());
            } else {
                $invoice->setAssigned($managerapproval);
            }
            $invoice->setInvoicedate($invoicedate);
            $invoice->setInvoicenumber($invoicenum);
            $invoice->setDescription($description);
            $invoice->setChartOfAccounts($chartofaccounts);
            $invoice->setDateupdated(new \DateTime());
            $invoicerepo->flush();

            $getcomments = new Invoicecomments();
            $getcomments->setComments($comments);
            $getcomments->setComments($comments);
            $getcomments->setAddedby($user->getId());
            $getcomments->setStatus('1');
            $getcomments->setInvoicecommentId($id);

            $commentsrepo = $this->getDoctrine()->getManager();
            $commentsrepo->persist($getcomments);
            $commentsrepo->flush();
        }

        // get value by id
        $invoiceimgrepo = $this->getDoctrine()->getManager();
        $invoiceimg     = $invoiceimgrepo->getRepository('ArcanysEasyAppBundle:InvoiceImages')
            ->findBy(['upltoken' => $invoice->getToken()], ['id' => 'DESC']);

        $vendorrepo1    = $this->getDoctrine()->getManager();
        $getvendor      = $vendorrepo1->getRepository('ArcanysEasyAppBundle:Vendor')
                                      ->find($invoice->getIdVendor());

        $entityrepo1    = $this->getDoctrine()->getManager();
        $getentity      = $entityrepo1->getRepository('ArcanysEasyAppBundle:Entity')
                                      ->find($invoice->getIdEntity());

        $userrepo       = $this->getDoctrine()->getManager();
        $getuser        = $userrepo->getRepository('ArcanysEasyAppBundle:User')
                                   ->findBy(  array('id' => $invoice->getManagerApproval()) );

        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getCharts      = $repository->findBy( array('id' => $invoice->getChartOfAccounts()) );

        $em2            = $this->getDoctrine()->getManager();
        $query2         = $em2->createQuery(
                            "SELECT
                                i.invoiceId, ic.comments, ic.addedby, ic.invoicecommentId, ic.dateadded,
                                u.firstname, u.roles
                            FROM ArcanysEasyAppBundle:Invoicecomments ic
                            LEFT JOIN ArcanysEasyAppBundle:User u
                                WITH ic.addedby = u.id
                            JOIN ArcanysEasyAppBundle:Invoice i
                                WITH ic.invoicecommentId = i.invoiceId
                            WHERE i.invoiceId = '" . $invoice->getInvoiceId() . "'
                            ORDER BY ic.dateadded DESC"
                        );
        $getComments    = $query2->getResult();

        // GET LIST OF VENDOR
        $vendorrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $query          = $vendorrepo->createQueryBuilder('v')
                                     ->where('v.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('v.dateadded', 'DESC')
                                     ->getQuery();
        $vendor         = $query->getResult();

        // GET LIST OF ENTITY
        $entityrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $query2         = $entityrepo->createQueryBuilder('e')
                                     ->where('e.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('e.dateadded', 'DESC')
                                     ->getQuery();
        $entity         = $query2->getResult();

        // GET LIST OF USERS
        $query3         = $this->getDoctrine()->getManager()
                               ->createQuery(
                                  "SELECT u
                                   FROM ArcanysEasyAppBundle:User u
                                   WHERE u.roles LIKE '%ROLE_MANAGER%' AND u.company = '" . $company[0] . "'");
        $user           = $query3->getResult();

        // GET LIST OF CHARTS OF ACCOUNTS
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getChartsdata  = $repository->findBy( array( 'company' => $company ) );

        return $this->render('ArcanysEasyAppBundle:Invoice:updatecheck.html.twig', array(
            'vendor'    => $vendor,
            'entity'    => $entity,
            'user'      => $user,
            'invoice'   => $invoice,
            'comment'   => $getComments,
            'fimage'    => reset($invoiceimg),
            'inimg'     => $invoiceimg,
            'getvendor' => $getvendor,
            'getentity' => $getentity,
            'getcharts' => $getCharts,
            'charts'    => $getChartsdata,
            'getuser'   => $getuser
        ));
    }

    public function draftupdatecheckAction($id, Request $request)
    {
        $session                = $request->getSession();
        $company                = $session->get('company');
        // get value by id
        $invoicerepo            = $this->getDoctrine()->getManager();
        $invoice                = $invoicerepo->getRepository('ArcanysEasyAppBundle:Invoice')->find($id);
        $user                   = $this->get('security.context')->getToken()->getUser();

        if ( $request->getMethod() == 'POST' ) {
            $entityname         = $request->get('entity');
            $datedue            = $request->get('datedue');
            $vendorname         = $request->get('vendor');
            $amount             = str_replace(array(',', '.00'), '', $request->get('amount'));
            $managerapproval    = $request->get('managerapproval');
            $invoicenum         = $request->get('invoiceamount');
            $checknum           = $request->get('checknum');
            $invoicedate        = $request->get('invoicedate');
            $description        = $request->get('description');
            $chartofaccounts    = $request->get('chartofaccounts');
            $comments           = $request->get('comments');
            $token              = $request->get('token');

            $repoentity         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
            $getEntz            = $repoentity->findOneBy( array('id' => $entityname) );

            $repoInvoice        = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Invoice');
            $getInvoice         = $repoInvoice->findBy( array('idVendor' => $vendorname, 'amount' => $amount) );

            $getAdmin           = $invoicerepo
                                    ->createQuery(
                                        "SELECT u
                                         FROM ArcanysEasyAppBundle:User u
                                         WHERE u.company = '" . $user->getCompany() . "' AND u.roles LIKE '%ROLE_ADMIN%'"
                                    )
                                    ->getResult();

            $invoice->setIdEntity($entityname);
            $invoice->setIdVendor($vendorname);
            $invoice->setDueDate($datedue);
            $invoice->setAmount($amount);
            $invoice->setCheckNo($checknum);
            $invoice->setStatus('1');
            $invoice->setManagerApproval($managerapproval);
            if ( $managerapproval == '99999' ) {
                $invoice->setAssigned($getAdmin[0]->getId());
            } else {
                $invoice->setAssigned($managerapproval);
            }
            $invoice->setInvoicedate($invoicedate);
            $invoice->setInvoicenumber($invoicenum);
            $invoice->setDescription($description);
            $invoice->setChartOfAccounts($chartofaccounts);
            $invoice->setDateupdated(new \DateTime());
            $invoicerepo->flush();

            $getcomments = new Invoicecomments();
            $getcomments->setComments($comments);
            $getcomments->setComments($comments);
            $getcomments->setAddedby($user->getId());
            $getcomments->setStatus('1');
            $getcomments->setInvoicecommentId($id);

            $commentsrepo = $this->getDoctrine()->getManager();
            $commentsrepo->persist($getcomments);
            $commentsrepo->flush();
        }

        // get value by id
        $invoiceimgrepo = $this->getDoctrine()->getManager();
        $invoiceimg     = $invoiceimgrepo->getRepository('ArcanysEasyAppBundle:InvoiceImages')
                                         ->findBy( array('upltoken' => $invoice->getToken()) );

        $vendorrepo1    = $this->getDoctrine()->getManager();
        $getvendor      = $vendorrepo1->getRepository('ArcanysEasyAppBundle:Vendor')->find($invoice->getIdVendor());

        $entityrepo1    = $this->getDoctrine()->getManager();
        $getentity      = $entityrepo1->getRepository('ArcanysEasyAppBundle:Entity')->find($invoice->getIdEntity());

        $userrepo       = $this->getDoctrine()->getManager();
        $getuser        = $userrepo->getRepository('ArcanysEasyAppBundle:User')
                                   ->findBy(  array('id' => $invoice->getManagerApproval()) );

        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getCharts      = $repository->findBy( array('id' => $invoice->getChartOfAccounts()) );

        $em2            = $this->getDoctrine()->getManager();
        $query2         = $em2->createQuery(
                            "SELECT
                                i.invoiceId, ic.comments, ic.addedby, ic.invoicecommentId, ic.dateadded,
                                u.firstname, u.roles
                            FROM ArcanysEasyAppBundle:Invoicecomments ic
                            LEFT JOIN ArcanysEasyAppBundle:User u
                                WITH ic.addedby = u.id
                            JOIN ArcanysEasyAppBundle:Invoice i
                                WITH ic.invoicecommentId = i.invoiceId
                            WHERE i.invoiceId = '" . $invoice->getInvoiceId() . "'
                            ORDER BY ic.dateadded DESC"
                        );
        $getComments    = $query2->getResult();

        // GET LIST OF VENDOR
        $vendorrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $query          = $vendorrepo->createQueryBuilder('v')
                                     ->where('v.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('v.dateadded', 'DESC')
                                     ->getQuery();
        $vendor         = $query->getResult();

        // GET LIST OF ENTITY
        $entityrepo     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
        $query2         = $entityrepo->createQueryBuilder('e')
                                     ->where('e.company = :company')
                                     ->setParameter('company', $company[0])
                                     ->orderBy('e.dateadded', 'DESC')
                                     ->getQuery();
        $entity         = $query2->getResult();

        // GET LIST OF USERS
        $query3         = $this->getDoctrine()->getManager()
                               ->createQuery(
                                   "SELECT u
                                    FROM ArcanysEasyAppBundle:User u
                                    WHERE u.roles LIKE '%ROLE_MANAGER%' AND u.company = '" . $company[0] . "'");
        $user           = $query3->getResult();

        // GET LIST OF CHARTS OF ACCOUNTS
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
        $getChartsdata  = $repository->findBy( array( 'company' => $company ) );

        return $this->render('ArcanysEasyAppBundle:Invoice:draftupdatecheck.html.twig', array(
            'vendor'    => $vendor,
            'entity'    => $entity,
            'user'      => $user,
            'invoice'   => $invoice,
            'comment'   => $getComments,
            'fimage'    => reset($invoiceimg),
            'inimg'     => $invoiceimg,
            'getvendor' => $getvendor,
            'getentity' => $getentity,
            'getcharts' => $getCharts,
            'charts'    => $getChartsdata,
            'getuser'   => $getuser
        ));
    }

    public function deleteAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';
        $em             = $this->getDoctrine()->getManager();
        $invoice        = $em->getRepository('ArcanysEasyAppBundle:Invoice')->find($getID);

        $invoice->setDeletestatus('0');
        $em->flush();

        $response       = array( "code" => 100, "success" => true, "id" => $invoice->getInvoiceId() );
        return new JsonResponse($response);
    }

    public function draftdeleteAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';
        $em             = $this->getDoctrine()->getManager();
        $invoice        = $em->getRepository('ArcanysEasyAppBundle:Invoice')->find($getID);

        $em->remove($invoice);
        $em->flush();

        $response       = array( "code" => 100, "success" => true, "id" => $invoice->getInvoiceId() );
        return new JsonResponse($response);
    }

    public function deleteimageAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';
        $invoiceimgrepo = $this->getDoctrine()->getManager();
        $invoiceimg     = $invoiceimgrepo->getRepository('ArcanysEasyAppBundle:InvoiceImages')->find($getID);

        if (file_exists($invoiceimg->getFileName())) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $this->container->getParameter('target_folder') . $invoiceimg->getFileName());
        }

        $invoiceimgrepo->remove($invoiceimg);
        $invoiceimgrepo->flush();

        $response       = array( "code" => 100, "success" => true );
        return new JsonResponse($response);
    }

    public function checkcurrentbalanceAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';
        $em             = $this->getDoctrine()->getManager();
        $entitybankinfo = $em->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                             ->find($getID);

        $response       = array(
                            "code"      => 100,
                            "success"   => true,
                            'bankname'  => $entitybankinfo->getBankName(),
                            'balance'   => $this->get('entity.values.handler')->getCurrentBalanceById($getID),
                            'acct'      => $entitybankinfo->getBankAcct(),
                            'checknum'  => $entitybankinfo->getStartBalance() );
        return new JsonResponse($response);
    }

    public function addcommentAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';
        $getData        = (isset($_POST['data'])) ? $_POST['data'] : '';
        $getUserID      = (isset($_POST['userid'])) ? $_POST['userid'] : '';

        $invoicecomment = new Invoicecomments();
        $invoicecomment->setInvoicecommentId($getID);
        $invoicecomment->setComments($getData);
        $invoicecomment->setAddedby($getUserID);
        $invoicecomment->setStatus('1');

        $em = $this->getDoctrine()->getManager();
        $em->persist($invoicecomment);
        $em->flush();

        $response       = array( "code" => 100, "success" => true );
        return new JsonResponse($response);
    }

    # status 0 = aprroved by manager    status 4 = aprroved by manager
    # status 1 = for approval           status 11 = admin's for approval
    # status 2 = not approve            status 22 = admin'snot approve
    # status 3 = partial                status 33 = admin's partial
    public function statusupdateAction()
    {
        # 0 ====> MANAGER
        # 1 ====> ADMIN
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';
        $getStatus      = (isset($_POST['status'])) ? $_POST['status'] : '';
        $roleuser       = (isset($_POST['roleuser'])) ? $_POST['roleuser'] : '';
        $em             = $this->getDoctrine()->getManager();
        $invoice        = $em->getRepository('ArcanysEasyAppBundle:Invoice')->find($getID);

//        $invoice->setStatus($getStatus);

        $status = '';
        if ( $getStatus == 0 || $getStatus == 4 ) {
            $status = 'Approved';
        } else if ( $getStatus == 2 ) {
            $status = 'Not Approved';
            $invoice->setStatus($getStatus);
        } else if ( $getStatus == 22 ) {
            $status = 'Not Approved';
        } else if ( $getStatus == 5 ) {
            $status = 'Partial Payment';
        }

        $em->flush();

        $response = array( "code" => 100, "success" => true, "status" => $status );
        return new JsonResponse($response);
    }

    public function checkbalanceAction()
    {
        $getCurBal      = str_replace(',', '', (isset($_POST['curbalance'])) ? $_POST['curbalance'] : '');
        $getInvoiceBal  = str_replace(',', '', (isset($_POST['invoicebal'])) ? $_POST['invoicebal'] : '');
        $status         = (isset($_POST['status'])) ? $_POST['status'] : '';
        $partialstat    = (isset($_POST['partialstat'])) ? $_POST['partialstat'] : '';
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';
        $user           = $this->get('security.context')->getToken()->getUser();
        $userRole       = $user->getRoles();

        if ( strpos($getCurBal, '.00') !== FALSE ) {
            $getCurBal = str_replace('.00', '', $getCurBal);
        }

        $repository     = $this->getDoctrine()->getManager();
        $getadmin       = $repository->
                            createQuery(
                                "SELECT u
                                 FROM ArcanysEasyAppBundle:User u
                                 WHERE u.company = '" . $user->getCompany() . "' AND u.roles LIKE '%ROLE_ADMIN%'"
                            )
                            ->getResult();
        $updateamount   = $repository->getRepository('ArcanysEasyAppBundle:Invoice')
                                     ->find($getID);

        /*var_dump($updateamount->getOutstandingbalance()); // null
        var_dump($updateamount->getAmount()); // 568567
        var_dump($getCurBal); // 568567.00
        var_dump($getInvoiceBal); // 568567
        var_dump($status);*/

        // MANAGER
        if ( $userRole[0] == 'ROLE_MANAGER' ) {
            if ( $status == 5 or $status == 54 ) {

                // IF OUTSTANDING BALANCE EXIST
                if ( $updateamount->getOutstandingbalance() ) {

                    // IF USER PAID WITH EXACT AMOUNT
                    if ( floatval($updateamount->getOutstandingbalance()) == floatval($getInvoiceBal) ||
                        floatval($getCurBal) == floatval($getInvoiceBal) ) {
                        $currentBalance2 = floatval($updateamount->getOutstandingbalance());
                        $invoiceBalance2 = floatval($getInvoiceBal);
                        $totalBalance2   = $currentBalance2 - $invoiceBalance2;
                        $info            = '0';
                        $message         = 'You have paid an exact amount YEAH';

                        $updateamount->setOutstandingbalance($totalBalance2);
                        $updateamount->setRemainingbalance($currentBalance2);
                        $updateamount->setStatus('44');
                        $updateamount->setDateupdated(new \DateTime());
                    }
                    else {
                        $currentBalance1 = floatval($updateamount->getOutstandingbalance());
                        $invoiceBalance1 = floatval($getInvoiceBal);
                        $totalBalance1   = $currentBalance1 - $invoiceBalance1;
                        $info            = '1';
                        $message         = 'Taah daaaaaah duh';

                        $updateamount->setOutstandingbalance($totalBalance1);
                        $updateamount->setStatus('54');
                        $updateamount->setDateupdated(new \DateTime());

                        self::processCallAction($getID, $invoiceBalance1);
                    }
                }

                // IF OUTSTANDING BALANCE IS NULL
                else {
                    if ( floatval($updateamount->getAmount()) == floatval($getInvoiceBal) ||
                        floatval($getCurBal) == floatval($getInvoiceBal) ) {
                        $currentBalance3 = floatval($updateamount->getAmount());
                        $invoiceBalance3 = floatval($getInvoiceBal);
                        $totalBalance3   = $currentBalance3 - $invoiceBalance3;
                        $info            = '0';
                        $message         = 'You have paid an exact amount YEAAAH';

                        $updateamount->setOutstandingbalance($totalBalance3);
                        $updateamount->setRemainingbalance($currentBalance3);
                        $updateamount->setStatus('44');
                        $updateamount->setDateupdated(new \DateTime());
                    }
                    else {
                        $currentBalance = floatval($getCurBal);
                        $invoiceBalance = floatval($getInvoiceBal);
                        $totalBalance   = $currentBalance - $invoiceBalance;
                        $info           = '2';
                        $message        = 'Outstanding balance is null DUUH! Created an invoice with the partial payment';

                        // update outstanding balance
                        $updateamount->setOutstandingbalance($totalBalance);
                        $updateamount->setStatus('54');
                        $updateamount->setDateupdated(new \DateTime());

                        self::processCallAction($getID, $invoiceBalance);
                    }
                }
            }

            // MANUAL PAYMENT
            else if ( $status == 3 || $status == 33 ) {
                $info       = '5';
                $message    = 'Damn! Rich kid.';
                $updateamount->setStatus('33');
                $updateamount->setDateupdated(new \DateTime());
            }

            else if ( $status == 2 || $status == 22 ) {
                $updateamount->setStatus('2');
                $updateamount->setDateupdated(new \DateTime());
                $info = '1';
                $message = 'status not approved by manager';
            }

            else if ( $status == 1 ) {
                $info = '';
                $message = 'status approved by manager';
                $updateamount->setStatus('0');
                $updateamount->setDateupdated(new \DateTime());
            }

            $updateamount->setAssigned($getadmin[0]->getId());
            $repository->flush();
        }

        else if ( $userRole[0] == 'ROLE_SUPER_ADMIN' || $userRole[0] == 'ROLE_ADMIN' ) {
            if ( $status == 5 or $status == 55 ) {

                // IF OUTSTANDING BALANCE EXIST
                if ( $updateamount->getOutstandingbalance() ) {

                    // IF USER PAID WITH EXACT AMOUNT
                    if ( floatval($updateamount->getOutstandingbalance()) == floatval($getInvoiceBal) ||
                        floatval($getCurBal) == floatval($getInvoiceBal) ) {
                        $currentBalance2 = floatval($updateamount->getOutstandingbalance());
                        $invoiceBalance2 = floatval($getInvoiceBal);
                        $totalBalance2   = $currentBalance2 - $invoiceBalance2;
                        $info            = '0';
                        $message         = 'You have paid an exact amount YEAH!!';

                        $updateamount->setOutstandingbalance($totalBalance2);
                        $updateamount->setRemainingbalance($currentBalance2);
                        $updateamount->setStatus('10');
                        $updateamount->setDateupdated(new \DateTime());
                    }
                    else {
                        $currentBalance1 = floatval($updateamount->getOutstandingbalance());
                        $invoiceBalance1 = floatval($getInvoiceBal);
                        $totalBalance1   = $currentBalance1 - $invoiceBalance1;
                        $info            = '1';
                        $message         = 'Taah daaaaaah!';

                        $updateamount->setOutstandingbalance($totalBalance1);
                        $updateamount->setStatus('55');
                        $updateamount->setDateupdated(new \DateTime());
                    }
                }

                // IF OUTSTANDING BALANCE IS NULL
                else {
                    $currentBalance = floatval($getCurBal);
                    $invoiceBalance = floatval($getInvoiceBal);
                    $totalBalance   = $currentBalance - $invoiceBalance;
                    $info           = '2';
                    $message        = 'Outstanding balance is null. Created an invoice with the partial payment';

                    // update outstanding balance
                    $updateamount->setOutstandingbalance($totalBalance);
                    $updateamount->setDateupdated(new \DateTime());

                    self::processCallAction($getID, $invoiceBalance);
                }

                $repository->flush();
            }

            // MANUAL PAYMENT
            else if ( $status == 44 || $status == 4 ) {
                $updateamount->setStatus('10');
                $updateamount->setDateupdated(new \DateTime());
                $repository->flush();
                $info = '0';
                $message = 'Damn! Rich kid!';
            }

            else if ( $status == 3 || $status == 33 ) {
                $updateamount->setStatus('10');
                $updateamount->setDateupdated(new \DateTime());
                $repository->flush();
                $info = '12';
                $message = 'status approved by admin without manager';
            }

            else if ( $status == 2 || $status == 22 ) {
                $updateamount->setStatus('22');
                $updateamount->setDateupdated(new \DateTime());
                $repository->flush();
                $info = '1';
                $message = 'status not approved by admin';
            }

            else if ( $status == 1 ) {
                $updateamount->setStatus('4');
                $updateamount->setDateupdated(new \DateTime());
                $repository->flush();
                $info = '3';
                $message = 'status approved by admin yea';
            }

            else if ( $status == 0 ) {
                $updateamount->setStatus('4');
                $updateamount->setDateupdated(new \DateTime());
                $repository->flush();
                $info = '3';
                $message = 'status approved by admin';
            }
        }

        $response = array( "success" => true, "info" => $info, "msg" => $message );
        return new JsonResponse($response);
    }

    public function vendordetailsAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
        $getVendor      = $repository->findOneBy( array('id' => $getID) );

        if ( $getVendor->getAddress() ) {
            $address = $getVendor->getAddress() . '<br/>';
            if ( $getVendor->getAddress() ) {
                $address .= $getVendor->getCity() . ', ' . $getVendor->getState() . ' ' . $getVendor->getZip();
            }
        }

        if ( $getVendor->getPaymentterm() ) {
            //$form = $getVendor->getPaymentterm();
            switch($getVendor->getPaymentterm()) {
                case 1: $form = "any"; break;
                case 2: $form = 10; break;
                case 3: $form = 15; break;
                case 4: $form = 30; break;
                case 5: $form = 45; break;
            }
        }

        $wform = '';
        $wformInfo = 0;
        if ( !$getVendor->getW9form() ) {
            $phonenum  = $getVendor->getPhoneNum();
            $formatted = "(".substr($phonenum,0,3).") ".substr($phonenum,3,3)."-".substr($phonenum,6);
            $wform     = 'No W9 on file, contact vendor. ' . $formatted;
            $wformInfo = 1;
        }

        $charts = '';
        if ( $getVendor->getChartsofaccounts() ) {
            $repository = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
            $getCharts  = $repository->findBy( array('id' => $getVendor->getChartsofaccounts()) );
            $chartsname = $getCharts[0]->getChartname();
            $charts     = $getVendor->getChartsofaccounts();
        }

        $response = array(
                        "success"   => true,
                        "name"      => $getVendor->getName(),
                        "address"   => $address,
                        "wform"     => $wform,
                        "info"      => $wformInfo,
                        "form"      => $form,
                        "charts"    => $charts,
                        "chartname" => $chartsname
                    );
        return new JsonResponse($response);
    }

    public function uploadAction()
    {
        if (empty($_FILES)) {
            return new JsonResponse(["code" => 100, "success" => false]);
        }

        $file       = $_FILES['file'];
        $token      = $this->get('request')->get('token');
        $formtoken  = $this->get('request')->get('formtoken');
        $pagenumber = $this->get('request')->get('pagenumber');

        // Validation
        $pathInfo = pathinfo($file['name']);
        if (!in_array($pathInfo['extension'], ['jpg','jpeg','gif','png','pdf'])) {
            return new JsonResponse(["code" => 100, "success" => false, 'msg' => 'Invalid file type.']);
        }

        $today = date("mdYGis");
        $rand  = rand(0, (date('mdYHi', strtotime('03-14-2012 09:06:00')) - date('mdYHi')));
        $targetFolder = $this->container->getParameter('target_folder'); // Relative to the root
        $targetFile = rtrim($_SERVER['DOCUMENT_ROOT'] . $targetFolder, '/') . '/' . md5($today)
            . $file['name'];

        try {
            @move_uploaded_file($file['tmp_name'], $targetFile);

            $image = new InvoiceImages();
            $image->setIdInvoice('0');
            $image->setFileName($fileName = md5($today) . $file['name']);
            $image->setStatus('1');
            $image->setUpltoken($token);

            if ($formtoken) {
                $image->setFormtoken($formtoken);
            }

            $image->setPagenumber($pagenumber ? $pagenumber : 1);

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
        $token = $this->get('request')->get('token');

        // Validation
        $pathInfo = pathinfo($file['name']);
        if (!in_array($pathInfo['extension'], ['jpg','jpeg','gif','png'])) {
            return new JsonResponse(["code" => 100, "success" => false, 'msg' => 'Invalid file type.']);
        }

        $invoice = $this->getDoctrine()
                        ->getRepository('ArcanysEasyAppBundle:Invoice')
                        ->findOneBy(['token' => $token]);
        $invoiceId = $invoice->getInvoiceId();

        $imagesCount = $this->getDoctrine()
                            ->getRepository('ArcanysEasyAppBundle:InvoiceImages')
                            ->countByInvoiceId($invoiceId);

        if ($imagesCount >= 10) {
            return new JsonResponse(["code" => 100, "success" => false, 'msg' => 'You are only allowed to upload up to 10 invoices.']);
        }

        $today = date("mdYGis");
        $rand  = rand(0, (date('mdYHi', strtotime('03-14-2012 09:06:00')) - date('mdYHi')));
        $targetFolder = $this->container->getParameter('target_folder');
        $targetFile = rtrim($_SERVER['DOCUMENT_ROOT'] . $targetFolder, '/') . '/' . md5($today)
            . $file['name'];

        try {
            @move_uploaded_file($file['tmp_name'], $targetFile);

            $image = new InvoiceImages();
            $image->setIdInvoice($invoiceId);
            $image->setFileName($fileName = md5($today) . $file['name']);
            $image->setStatus('1');
            $image->setUpltoken($token);

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

    public function uploadmultiformAction()
    {
        if (empty($_FILES)) {
            return new JsonResponse(["code" => 100, "success" => false]);
        }

        $file = $_FILES['file'];
        $token = $this->get('request')->get('token');

        // Validation
        $pathInfo = pathinfo($file['name']);
        if (!in_array($pathInfo['extension'], ['jpg','jpeg','gif','png'])) {
            return new JsonResponse(["code" => 100, "success" => false, 'msg' => 'Invalid file type.']);
        }

        $invoice = $this->getDoctrine()
            ->getRepository('ArcanysEasyAppBundle:Invoice')
            ->findOneBy(['token' => $token]);
        $invoiceId = $invoice ? $invoice->getInvoiceId(): 0;

        $today = date("mdYGis");
        $rand = rand(0, (date('mdYHi', strtotime('03-14-2012 09:06:00')) - date('mdYHi')));
        $targetFolder = $this->container->getParameter('target_folder');
        $targetFile = rtrim($_SERVER['DOCUMENT_ROOT'] . $targetFolder, '/') . '/' . md5($today)
            . $file['name'];

        try {
            @move_uploaded_file($file['tmp_name'], $targetFile);

            $image = new InvoiceImages();
            $image->setIdInvoice($invoiceId);
            $image->setFileName($fileName = md5($today) . $file['name']);
            $image->setStatus('1');
            $image->setUpltoken($token);

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

    public function retrieveimageAction()
    {
        $request            = $this->get('request');
        $uplToken           = $request->get('uplToken');

        $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:InvoiceImages');
        $getInvoiceimg      = $repository->findBy( array('upltoken' => $uplToken) );

        $str    = array();
        $id     = array();
        foreach ($getInvoiceimg as $key => $value) {
            end($getInvoiceimg);
            if ($key === key($getInvoiceimg))
                $str[]  = $value->getFileName();
                $id[]   = $value->getId();
        }
        $response           = array( "success" => true, "image" => $str, "id" => $id );
        return new JsonResponse($response);
    }

    public function retrievenewimageAction()
    {
        $request            = $this->get('request');
        $token              = $request->get('uplToken');

        $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:InvoiceImages');
        $getInvoiceimg      = $repository->findBy( array('upltoken' => $token) );

        $str    = array();
        $id     = array();
        //$image  = array();
        foreach ($getInvoiceimg as $key => $value) {
            end($getInvoiceimg);
            if ($key === key($getInvoiceimg))
                $str[]  = $value->getFileName();
                $id[]   = $value->getId();

            $image = explode( '+[]+', implode($str) );
        }

        $response = array( "success" => true, "image" => $str, "id" => $id );
        return new JsonResponse($response);
    }

    public function invoiceduplicateAction()
    {
        $request            = $this->get('request');
        $amount             = $request->get('amount');
        $vendor             = $request->get('vendor');

        $em                 = $this->getDoctrine()->getManager();
        $query              = $em->createQuery(
                                        "SELECT i.invoicenumber, i.invoiceId, i.idVendor, i.amount, i.dateadded
                                        FROM ArcanysEasyAppBundle:Invoice i
                                        WHERE i.amount = '" . $amount . "' AND i.idVendor = '" . $vendor . "'
                                        ORDER BY i.dateadded DESC"
                                    );
        $getInvoice            = $query->getResult();

        $invoiceId  = array();
        $invoiceNum = array();
        foreach ( $getInvoice as $invoice ) {
            $invoiceId  = $invoice['invoiceId'];
            $invoiceNum = $invoice['invoicenumber'];
        }

        $message = '';
        $info = 0;
        if ( empty($getInvoice) ) {
            $info    = 0;
            $message = '';
        } else {
            if ( !empty($invoiceNum) ) {
                $info    = 1;
                $url     = $this->generateUrl('EA_detail_invoice', array('id' => $invoiceId));
                $message = 'Check amount entered is duplicate. <a href="' . $url . '" target="_blank">' . $invoiceNum . '</a>';
            }
        }

        $response       = array( "code" => 100, "success" => true, 'message' => $message, 'info' => $info );
        return new JsonResponse($response);
    }

    public function checkduplicateAction()
    {
        $request            = $this->get('request');
        $amount             = trim($request->get('amount'));
        $vendor             = trim($request->get('vendor'));
        $entity             = trim($request->get('entity'));
        $invoicenum         = trim($request->get('invoicenum'));

        $em             = $this->getDoctrine()->getManager();
        $query          = $em->createQuery(
                                    "SELECT i
                                    FROM ArcanysEasyAppBundle:Invoice i
                                    WHERE i.invoicenumber = '" . $invoicenum . "'
                                    ORDER BY i.dateadded DESC"
                                );
        $getInvoice    = $query->getResult();

        $invoiceId  = array();
        $invoiceNum = array();
        foreach ($getInvoice as $invoice) {
            $invoiceId  = $invoice->getInvoiceId();
            $invoiceNum = $invoice->getInvoicenumber();
            $status     = $invoice->getStatus();
        }

        $message = '';
        $info    = 0;
        if ( empty($getInvoice) ) { // if invoice is empty
            $message = '';
            $info = 0;
        }

        else if ( $status == 2 ) { // if status is not approved
            $url      = $this->generateUrl('EA_detail_invoice', array('id' => $invoiceId));
            $message  = 'Invoice number entered is a duplicate and not approved. <a href="' . $url . '" target="_blank">' . $invoiceNum . '</a>';
            $info     = 1;
        }

        else {
            $url      = $this->generateUrl('EA_detail_invoice', array('id' => $invoiceId));
            $message  = 'Invoice number entered is a duplicate. <a href="' . $url . '" target="_blank">' . $invoiceNum . '</a>';
            $info     = 1;
        }

        $response = array( "code" => 100, "success" => true, 'message' => $message, 'info' => $info );
        return new JsonResponse($response);
    }

    public function retrievemultidataAction()
    {
        $pagenumber     = (isset($_POST['pagenumber'])) ? $_POST['pagenumber'] : '';
        $formtoken      = (isset($_POST['formtoken'])) ? $_POST['formtoken'] : '';
        $token          = (isset($_POST['token'])) ? $_POST['token'] : '';
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Invoice');
        $getInvoicedata = $repository->findBy( array('formtoken' => $formtoken, 'pagenumber' => $pagenumber) );
        $invoiceImages  = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:InvoiceImages')
                               ->findBy(['pagenumber' => ($pagenumber ? $pagenumber : [null, 1]), 'formtoken' => $formtoken]);

        $images = [];
        foreach($invoiceImages as $key => $image) {
            $images[] = [
                'id' => $image->getId(),
                'fileName' => $image->getFilename()
            ];
        }

        // FILTER IF EMPTY DATA
        // GET INVOICE DATA
        if ( empty($getInvoicedata) ) {
            $invoiceEntity          = '';
            $invoiceChecknum        = '';
            $invoiceDuedata         = '';
            $invoiceVendor          = '';
            $invoiceAmount          = '';
            $invoiceInvoicenum      = '';
            $invoiceInvoicedate     = '';
            $invoiceManagerapproval = '';
            $invoiceDescription     = '';
            $invoiceCharts          = '';
            $invoiceToken           = '';
        } else {
            $repository1    = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Entity');
            $getEntity      = $repository1->findBy( array('id' => $getInvoicedata[0]->getIdEntity()) );

            $repository2    = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Vendor');
            $getVendor      = $repository2->findBy( array('id' => $getInvoicedata[0]->getIdVendor()) );

            $repository3    = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:InvoiceImages');
            $getImages      = $repository3->findBy( array('upltoken' => $getInvoicedata[0]->getToken()) );

            $repository4    = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Chartofaccounts');
            $getCharts      = $repository4->findBy( array('id' => $getInvoicedata[0]->getChartOfAccounts()) );

            $repository5    = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Invoicecomments');
            $getComments    = $repository5->findBy( array('invoicecommentId' => $getInvoicedata[0]->getInvoiceId()) );

            $invoiceEntity          = $getInvoicedata[0]->getIdEntity();
            $invoiceChecknum        = $getInvoicedata[0]->getCheckNo();
            $invoiceDuedata         = $getInvoicedata[0]->getDueDate();
            $invoiceVendor          = $getInvoicedata[0]->getIdVendor();
            $invoiceAmount          = $getInvoicedata[0]->getAmount();
            $invoiceInvoicenum      = $getInvoicedata[0]->getInvoicenumber();
            $invoiceInvoicedate     = $getInvoicedata[0]->getInvoicedate();
            $invoiceManagerapproval = $getInvoicedata[0]->getManagerApproval();
            $invoiceDescription     = $getInvoicedata[0]->getDescription();
            $invoiceCharts          = $getInvoicedata[0]->getChartOfAccounts();
            $invoiceToken           = $getInvoicedata[0]->getToken();
        }

        // GET ENTITY DATA
        if ( empty($getEntity) ) {
            $entityBalance  = '';
            $entityBankacct = '';
            $entityBankname = '';
        } else {
            $entityBalance  = $getEntity[0]->getCurBalance();
            $entityBankacct = $getEntity[0]->getBankAcct();
            $entityBankname = $getEntity[0]->getBankName();
        }

//        var_dump($getVendor); exit();
        if ( empty($getVendor) ) {
            $vendorName     = '';
            $vendorAddress  = '';
            $vendorCharts   = '';
        } else {
            $vendorName     = $getVendor[0]->getName();
            $vendorAddress  = $getVendor[0]->getAddress();
            $vendorCharts   = $getVendor[0]->getId();
        }

        if ( empty($getComments) ) {
            $commentsData   = '';
        } else {
            $commentsData   = $getComments[0]->getComments();
        }

        if ( !empty($getInvoicedata) && $getInvoicedata[0]->getPagenumber() == $pagenumber ) {
            if ( empty($getImages) ) {
                $setImg  = 'No image available';
                $setFlag = 0;
            } else {
                $str = array();
                foreach ($getImages as $key => $value) {
                    $str[] = $value->getFileName();
                }
                $setImg  = $str;
                $setFlag = 1;
            }

            $response = array(
                "success"   => true,
                "entity"    => $invoiceEntity,
                "checknum"  => $invoiceChecknum,
                "duedate"   => $invoiceDuedata,
                "vendor"    => $invoiceVendor,
                "amount"    => $invoiceAmount,
                "invoicen"  => $invoiceInvoicenum,
                "invoiced"  => $invoiceInvoicedate,
                "charts"    => $invoiceCharts,
                "manager"   => $invoiceManagerapproval,
                "descript"  => $invoiceDescription,
                "token"     => $invoiceToken,
                "curbal"    => $entityBalance,
                "acct"      => $entityBankacct,
                "bankname"  => $entityBankname,
                "vname"     => $vendorName,
                "vaddress"  => $vendorAddress,
                "image"     => $setImg,
                "imgcheck"  => $setFlag,
                "comments"  => $commentsData,
                'images'    => $images
            );

        } else {
            $response = ["success" => true, "output" => "empty", 'images' => $images];
        }

        return new JsonResponse($response);
    }

    public function updatetopendingAction()
    {
        $id             = (isset($_POST['id'])) ? $_POST['id'] : '';
        $repository     = $this->getDoctrine()->getManager();
        $getInvoicedata = $repository->getRepository('ArcanysEasyAppBundle:Invoice')
                                     ->find($id);

        $getInvoicedata->setStatus('1');
        $repository->persist($getInvoicedata);
        $repository->flush();

        $response = array( "success" => true );
        return new JsonResponse($response);
    }

    public function sendemailAction(Request $request)
    {
        $email          = (isset($_POST['email'])) ? $_POST['email'] : ''; // email, email2, email3
        $manager        = (isset($_POST['manager'])) ? $_POST['manager'] : '';
        $admin          = (isset($_POST['admin'])) ? $_POST['admin'] : '';
        $body           = (isset($_POST['body'])) ? $_POST['body'] : '';

        if ( $request->getMethod() == 'POST' ) {
            $newemail = explode(',', $email);
            if ( $email[0] == ',' ) {
                var_dump(array_splice($newemail, 0, 1));
            }

            if ( $manager == 0 && $admin != 0 ) {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Archive View')
                    ->setFrom('no-reply@easyapp.com')
                    ->setTo($newemail)
                    ->setCc('admin@gmail.com')
                    ->setBody(
                        $this->renderView(
                            'ArcanysEasyAppBundle:Invoice:emailtemplate.html.twig', array('body' => $body)
                        ),
                        'text/html');
            }
            else if ( $manager != 0 && $admin == 0 ) {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Archive View')
                    ->setFrom('no-reply@easyapp.com')
                    ->setTo($newemail)
                    ->setCc($manager)
                    ->setBody(
                        $this->renderView(
                            'ArcanysEasyAppBundle:Invoice:emailtemplate.html.twig', array('body' => $body)
                        ),
                        'text/html');
            }
            else if ( $manager && $admin ) {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Archive View')
                    ->setFrom('no-reply@easyapp.com')
                    ->setTo($newemail)
                    ->setCc( array( $manager => 'Manager', 'admin@gmail.com' => 'Admin' ) )
                    ->setBody(
                        $this->renderView(
                            'ArcanysEasyAppBundle:Invoice:emailtemplate.html.twig', array('body' => $body)
                        ),
                        'text/html');
            }
            else {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Archive View')
                    ->setFrom('send@example.com')
                    ->setTo($newemail)
                    ->setBody(
                        $this->renderView(
                            'ArcanysEasyAppBundle:Invoice:emailtemplate.html.twig', array('body' => $body)
                        ),
                        'text/html');
            }

            $this->get('mailer')->send($message);
        }

        $response = array( "success" => true );
        return new JsonResponse($response);
    }

    public function manualbankinfoAction()
    {
        $getId           = (isset($_POST['id'])) ? $_POST['id'] : '';
        $value           = (isset($_POST['value'])) ? $_POST['value'] : '';
        $checknum        = (isset($_POST['checknum'])) ? $_POST['checknum'] : '';
        $repository      = $this->getDoctrine()->getManager();
        $updatebankinfo  = $repository->getRepository('ArcanysEasyAppBundle:Invoice')
                                      ->find($getId);
        $entitybankinfo  = $repository->getRepository('ArcanysEasyAppBundle:Entitybankinfo')
                                      ->findBy( array( 'entityNumId' => $updatebankinfo->getIdEntity() ) );
        $getChecknum     = $repository->getRepository('ArcanysEasyAppBundle:Entitychecknum')
                                      ->findBy( array('checknum' => $entitybankinfo[0]->getStartBalance()) );
        $chkChecknum     = $repository->getRepository('ArcanysEasyAppBundle:Entitychecknum')
                                      ->findBy( array('chkInvoiceid' => $getId) ); // check if data exist

        if ( empty($getChecknum) ) {
            $addChecknum = $entitybankinfo[0]->getStartBalance();
        } else {
            $addChecknum = $entitybankinfo[0]->getStartBalance() + 1;
        }

        if ( empty($chkChecknum) ) {
            // INSERT STARTING ENTITY CHECK NUMBER
            $entitycheck = new Entitychecknum();
            $entitycheck->setChkInvoiceid($getId);
            $entitycheck->setStatus('1');
            $entitycheck->setChecknum($addChecknum);

            // UPDATE CHECKNUMBER OF INVOICE
            $updatebankinfo->setCheckNo($addChecknum);
            $updatebankinfo->setDateupdated(new \DateTime());

            // UPDATE DATA OF BANK INFORMATION OF INVOICE
            $invoicebankinfo = new Invoiceebankinfo();
            $invoicebankinfo->setEntitybankinfoId($value);
            $invoicebankinfo->setInvoiceinfoId($getId);
            $invoicebankinfo->setEntityId($updatebankinfo->getIdEntity());

            $entitychkrepo = $this->getDoctrine()->getManager();
            $entitychkrepo->persist($entitycheck);
            $entitychkrepo->persist($updatebankinfo);
            $entitychkrepo->persist($invoicebankinfo);
            $entitychkrepo->flush();
        }


        if ( $value == 'other' ) {
            $updatebankinfo->setBankinfo('1');
            $updatebankinfo->setEntityready('1');
            $updatebankinfo->setPrintready('1');
        } else {
            $updatebankinfo->setEntityready('1');
            $updatebankinfo->setPrintready('1');
        }
        $repository->flush();

        $response = array( "success" => true );
        return new JsonResponse($response);
    }

    public function processCallAction($getID, $invoiceBalance)
    {
        // create new partial invoice and pointed to the approved section
        // ready for printing
        // copy new invoice
        $user        = $this->get('security.context')->getToken()->getUser();
        $newrepo     = $this->getDoctrine()->getManager();
        $copyinvoice = $newrepo->getRepository('ArcanysEasyAppBundle:Invoice')
                               ->find($getID);

        $invoice = new Invoice();
        $invoice->setIdEntity($copyinvoice->getIdEntity());
        $invoice->setIdVendor($copyinvoice->getIdVendor());
        $invoice->setCheckNo($copyinvoice->getCheckNo());
        $invoice->setDueDate($copyinvoice->getDueDate());
        $invoice->setAmount($copyinvoice->getAmount());
        $invoice->setStatus('44');
        $invoice->setManagerApproval($copyinvoice->getManagerApproval());
        $invoice->setAddedby($copyinvoice->getAddedby());
        $invoice->setInvoicedate($copyinvoice->getInvoicedate());
        $invoice->setInvoicenumber($copyinvoice->getInvoicenumber());
        $invoice->setDescription($copyinvoice->getDescription());
        $invoice->setDeletestatus('1');
        $invoice->setReadstatus('1');
        $invoice->setChartOfAccounts($copyinvoice->getChartOfAccounts());
        $invoice->setToken($copyinvoice->getToken());
        $invoice->setOutstandingbalance($invoiceBalance);
        $invoice->setDateupdated(new \Datetime());
        $invoice->setCompany($user->getCompany());

        $em = $this->getDoctrine()->getManager();
        $em->persist($invoice);
        $em->flush();

        // copy new comments
        $newcommentrepo = $this->getDoctrine()->getManager();
        $copycomment    = $newcommentrepo->getRepository('ArcanysEasyAppBundle:Invoicecomments')
            ->findBy( array( 'invoicecommentId' => $copyinvoice->getInvoiceId() ) );

        if ( !empty($copycomment) ) {
            $getcomments = new Invoicecomments();
            $getcomments->setComments($copycomment[0]->getComments());
            $getcomments->setAddedby($copycomment[0]->getAddedby());
            $getcomments->setStatus('1');
            $getcomments->setInvoicecommentId($copycomment[0]->getInvoicecommentId());

            $commentsrepo = $this->getDoctrine()->getManager();
            $commentsrepo->persist($getcomments);
            $commentsrepo->flush();
        }

        // copy new images
        $invoiceimgrepo = $this->getDoctrine()->getManager();
        $invoiceimg     = $invoiceimgrepo->getRepository('ArcanysEasyAppBundle:InvoiceImages')
                                         ->findBy( array('idInvoice' => $copyinvoice->getInvoiceId()) );

        if ( !empty($invoiceimg) ) {
            $getimages = new InvoiceImages();
            $getimages->setIdInvoice($invoiceimg[0]->getIdInvoice());
            $getimages->setFileName($invoiceimg[0]->getFileName());
            $getimages->setStatus($invoiceimg[0]->getStatus());
            $getimages->setUpltoken($invoiceimg[0]->getUpltoken());

            $em2 = $this->getDoctrine()->getManager();
            $em2->persist($getimages);
            $em2->flush();
        }
    }

}