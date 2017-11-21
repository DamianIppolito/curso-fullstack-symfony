<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    public function loginAction(Request $request){
	    $helpers = $this->get("app.helpers");
	    $jwt_auth = $this->get("app.jwt_auth");

	    $json = $request->get("json", null);
		if(!is_null($json)){
			$params = json_decode($json);
			$email = (isset($params->email)) ? $params->email : null;
			$password = (isset($params->password)) ? $params->password : null;
			$getHash = (isset($params->getHash)) ? $params->getHash : null;

			$emailConstraint = new Email();
			$emailConstraint->message = "Email format not valid!!";
			$validate_email = $this->get('validator')->validate($email, $emailConstraint);

			if (count($validate_email) == 0 && !is_null($password)){
				$signup = $jwt_auth->signup($email,$password,$getHash);
				return new JsonResponse($signup);
			}else{
				return $helpers->json(array("status" => "error","data" => "Login not valid!!"));
			}
		}else{
			return $helpers->json(array("status" => "error","data" => "Send JSON with POST!!"));
		}
    }

	public function pruebasAction(Request $request)
	{
		$helpers = $this->get("app.helpers");
		$hash = $request->get('authorization', null);
		$check = $helpers->authCheck($hash,true);
		var_dump($check);
		die();
//		$em = $this->getDoctrine()->getManager();
//		$users = $em->getRepository('BackendBundle:User')->findAll();
//		return $helpers->json($users);
	}
}
