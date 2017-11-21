<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
	    $json = $request->get("json", null);
		if(!is_null($json)){
			$params = json_decode($json);
			$email = (isset($params->email)) ? $params->email : null;
			$password = (isset($params->password)) ? $params->password : null;

			$emailConstraint = new Email();
			$emailConstraint->message = "Email format not valid!!";
			$validate_email = $this->get('validator')->validate($email, $emailConstraint);

			if (count($validate_email) == 0 && !is_null($password)){
				echo "Data Success";
			}else{
				echo "Data incorret";
			}
		}else{
			echo "Send JSON with POST";
			die();
		}
    }

	public function pruebasAction(Request $request)
	{
		$helpers = $this->get("app.helpers");
		$em = $this->getDoctrine()->getManager();
		$users = $em->getRepository('BackendBundle:User')->findAll();
		return $helpers->json($users);
	}
}
