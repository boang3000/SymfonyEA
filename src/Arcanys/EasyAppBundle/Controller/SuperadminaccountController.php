<?php

namespace Arcanys\EasyAppBundle\Controller;

use Arcanys\EasyAppBundle\Entity\User;
use Arcanys\EasyAppBundle\Entity\Userimage;
use Arcanys\EasyAppBundle\Form\Type\RegistrationFormType;
use Arcanys\EasyAppBundle\Form\Type\AdminRegistrationFormType;
use Arcanys\EasyAppBundle\Form\Type\ChangepassFormType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class SuperadminaccountController extends Controller
{
    public function checkemailAction()
    {
        $getEmail       = (isset($_POST['keyword'])) ? $_POST['keyword'] : '';

        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:User');
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
            $info = 1;
        }

        $response       = array( "code" => 100, "success" => true, 'email' => $emailresponse, 'info' => $info );
        return new JsonResponse($response);
    }

    public function createAction(Request $request)
    {
        $session = $this->get('session');
        $session->start();
        $sessID = $session->getId();
        $sessName = $session->getName();

        $today              = date('mdYHi');
        $startDate          = date('mdYHi', strtotime('03-14-2012 09:06:00'));
        $range              = $startDate - $today;
        $rand               = rand(0, $range);
        $generatecheck      = md5($rand . ($startDate + $rand));

        $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:User');
        $users              = $repository->findByRole('ROLE_ADMIN');
		
		$form = $this->createForm(new AdminRegistrationFormType(), new User());

        $form->handleRequest($request);
        if($form->isSubmitted()) {
            if($form->isValid()) {

                $user = $form->getData();
                $user->setUsername($user->getEmail());
                $user->setEnabled($user->isEnabled());

                $factory        = $this->get('security.encoder_factory');
                $encoder        = $factory->getEncoder($user);
                $password       = $encoder->encodePassword($user->getPassword(), $user->getSalt());

                $user->setPassword($password);
                $user->setToken($_POST['token']);
				$user->setRoles(array('ROLE_ADMIN'));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:User');
                $getUser        = $repository->findOneBy( array('username' => $user->getUsername()) );

                $url = $this->generateUrl('EA_superadmin_displayaccount', array('id' => $user->getId()));
                return $this->redirect($url);
            }
        }

        return $this->render('ArcanysEasyAppBundle:AdminaccountDashboard:addaccountAdmin.html.twig', array(
            'form'      => $form->createView(),
            'users'     => $users,
            'checknum'  => $generatecheck,
            'sessionid' => $sessID,
            'sessName'  => $sessName
        ));
    }

    public function uploadAction()
    {
        $session = $this->get('session');
        $request            = $this->get('request');
        $checknum           = $request->get('token');
        $sessionid          = $request->get('session');

        /*if (array_key_exists('session', $request))
            session_id($sessionid);*/

        $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Userimage');
        $getUserimg         = $repository->findBy( array('token' => $checknum) );

        $targetFolder = $this->container->getParameter('target_folder'); // Relative to the root

        if (!empty($_FILES)) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
            $targetFile = rtrim($targetPath,'/') . '/' . md5(date("mdYGis")) . $_FILES['Filedata']['name'];

            // Validate the file type
            $fileTypes = array('png'); // File extensions
            $fileParts = pathinfo($_FILES['Filedata']['name']);

            if (in_array($fileParts['extension'], $fileTypes)) {
                move_uploaded_file($tempFile, $targetFile);

                /*if ( empty($getUserimg) ) {*/
                    $userimage = new Userimage();
                    $userimage->setUserid('00');
                    $userimage->setToken($checknum);
                    $userimage->setFilename(md5(date("mdYGis")) . $_FILES['Filedata']['name']);
                    $userimage->setStatus('1');

                    $em2 = $this->getDoctrine()->getManager();
                    $em2->persist($userimage);
                    $em2->flush();
                /*} else {
                    $em = $this->getDoctrine()->getManager();
                    $updateUserimg = $em->getRepository('ArcanysEasyAppBundle:Userimage')->find($getUserimg[0]->getId());
                    $updateUserimg->setFilename(md5(date("mdYGis")) . $_FILES['Filedata']['name']);
                    $em->flush();
                }*/

            } else {
                echo 'Invalid file type.';
            }
        }

        $response       = array( "code" => 100, "success" => true );
        return new JsonResponse($response);
    }

    public function edituploadAction()
    {
        $request            = $this->get('request');
        $userid             = $request->get('id');

        $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:User');
        $getUser            = $repository->findBy( array('id' => $userid) );

        $repository2        = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Userimage');
        $getUserimg         = $repository2->findBy( array('token' => $getUser[0]->getToken()) );

        $targetFolder = $this->container->getParameter('target_folder'); // Relative to the root

        if (!empty($_FILES)) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
            $targetFile = rtrim($targetPath,'/') . '/' . md5(date("mdYGis")) . $_FILES['Filedata']['name'];

            // Validate the file type
            $fileTypes = array('png'); // File extensions
            $fileParts = pathinfo($_FILES['Filedata']['name']);

            if (in_array($fileParts['extension'], $fileTypes)) {
                move_uploaded_file($tempFile, $targetFile);

                if ( empty($getUserimg) ) {
                    $userimage = new Userimage();
                    $userimage->setUserid('00');
                    $userimage->setToken($getUser[0]->getToken());
                    $userimage->setFilename(md5(date("mdYGis")) . $_FILES['Filedata']['name']);
                    $userimage->setStatus('1');

                    $em2 = $this->getDoctrine()->getManager();
                    $em2->persist($userimage);
                    $em2->flush();
                } else {
                    $em = $this->getDoctrine()->getManager();
                    $updateUserimg = $em->getRepository('ArcanysEasyAppBundle:Userimage')->find($getUserimg[0]->getId());
                    $updateUserimg->setFilename(md5(date("mdYGis")) . $_FILES['Filedata']['name']);
                    $em->flush();
                }

            } else {
                echo 'Invalid file type.';
            }
        }

        $response       = array( "code" => 100, "success" => true );
        return new JsonResponse($response);
    }

    public function viewajaxAction()
    {
        $getID      = (isset($_POST['id'])) ? $_POST['id'] : '';
        $form       = $this->createForm(new RegistrationFormType(), new User());

        $repository = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:User');
        $users      = $repository->find($getID);

        $repository2    = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Userimage');
        $userimg        = $repository2->findBy( array('token' => $users->getToken()) );

        return $this->render('ArcanysEasyAppBundle:AdminaccountDashboard:displayAccount.html.twig',array(
            'form'      => $form->createView(),
            'users'     => $users,
            'userimg'   => $userimg
        ));
    }

    public function viewAction($id)
    {
        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:User');
        $vusers         = $repository->findByRole('ROLE_ADMIN');

        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:User');
        $users          = $repository->find($id);

        $repository2    = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Userimage');
        $userimg        = $repository2->findBy( array('token' => $users->getToken()) );

        return $this->render('ArcanysEasyAppBundle:AdminaccountDashboard:viewAccountAdmin.html.twig',array(
            'users'     => $users,
            'vusers'    => $vusers,
            'userimg'   => $userimg
        ));
    }

    public function updateAction($id, Request $request)
    {
        $em             = $this->getDoctrine()->getManager();
        $user           = $em->getRepository('ArcanysEasyAppBundle:User')->find($id);

        if ( $request->getMethod() == 'POST' ) {
            $firstname  = $request->get('firstname');
            $lastname   = $request->get('lastname');
            $email      = $request->get('email');

            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setEmail($email);
            $user->setEmailCanonical($email);
            $user->setUsername($email);
            $user->setUsernameCanonical($email);

            $em->flush();

            return new JsonResponse(['code' => 100, 'success' => true]);
        }

    }

    public function updatepasswordAction($id, Request $request)
    {
        $form = $this->createForm(new ChangepassFormType(), new User());

        $form->handleRequest($request);
        if($form->isSubmitted()) {
//            if($form->isValid()) {
                $user = $form->getData();

                $em = $this->getDoctrine()->getManager();
                $getuser = $em->getRepository('ArcanysEasyAppBundle:User')->find($id);

                $factory        = $this->get('security.encoder_factory');
                $encoder        = $factory->getEncoder($getuser);
                $password       = $encoder->encodePassword($user->getPlainPassword(), $getuser->getSalt());

                if ($user->getPlainPassword()) {

                }
                $getuser->setPassword($password);
//                var_dump($getuser->getPassword());

                $em->flush();
        }

        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:User');
        $users          = $repository->find($id);

        return $this->render('ArcanysEasyAppBundle:AdminaccountDashboard:changepass.html.twig',array(
            'users'  => $users,
            'form'   => $form->createView()
        ));
    }

    public function forgotpasswordAction($id, Request $request)
    {
        $form = $this->createForm(new ChangepassFormType(), new User());
        $form->handleRequest($request);
        $user = $form->getData();

        $em         = $this->getDoctrine()->getManager();
        $getuser    = $em->getRepository('ArcanysEasyAppBundle:User')->find($id);
        var_dump($user);


        $message = \Swift_Message::newInstance()
            ->setSubject('EasyAP: Your current password')
            ->setTo($getuser->getEmail())
            ->setFrom(
                array (
                    ( $this->container->hasParameter('mailer_username') ?
                        $this->container->getParameter('mailer_username') :
                        'no-reply@gmail.com' ) => 'MEZ2Link'
                )
            )
            ->setBody('Your current password is ' . $getuser->getPlainPassword(), 'text/html');

        $this->container->get('mailer')->send($message);

        return new Response($getuser->getPassword());
    }

    public function deleteAction()
    {
        $getID          = (isset($_POST['id'])) ? $_POST['id'] : '';

        $em             = $this->getDoctrine()->getManager();
        $user           = $em->getRepository('ArcanysEasyAppBundle:User')->find($getID);

        $em->remove($user);
        $em->flush();

        $repository     = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:User');
        $query          = $repository->createQueryBuilder('e')
                                    ->orderBy('e.id', 'DESC')
                                    ->setMaxResults(1)
                                    ->getQuery();
        $getuser      = $query->getResult();

        $getEntityID    = '';
        if ($getuser =! NULL) {
            $getUserID = '';
        } else {
            $getUserID = $getuser[0]->getId();
        }

        $response        = array( "code" => 100, "success" => true, "id" => $getUserID );
        return new Response( json_encode($response) );
    }

    public function retrieveaccountimgAction()
    {
        $request            = $this->get('request');
        $token              = $request->get('token');

        $repository         = $this->getDoctrine()->getRepository('ArcanysEasyAppBundle:Userimage');
        $getUserimg         = $repository->findOneBy( array('token' => $token) );

        $response       = array( "success" => true, "name" => $getUserimg->getFileName() );
        return new JsonResponse($response);
    }

}
