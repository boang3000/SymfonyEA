<?php

namespace Arcanys\EasyAppBundle\Controller;

use Arcanys\EasyAppBundle\Entity\InvoiceImages;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class InvoiceImagesController extends Controller
{
    public function updateVendorAction(Request $request)
    {
        $id = $request->get('id');
        $idVendor = $request->get('idVendor');

        $em = $this->getDoctrine()->getManager();
        $invoiceImage = $em->getRepository('ArcanysEasyAppBundle:InvoiceImages')->find($id);
        $invoiceImage->setIdVendor($idVendor);

        try {
            $em->persist($invoiceImage);
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['code' => 100, 'success' => false, 'msg' => $e->getMessage()]);
        }

        return new JsonResponse(['code' => 100, 'success' => true]);
    }
}