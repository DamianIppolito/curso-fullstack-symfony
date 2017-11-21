<?php

namespace AppBundle\Controller;

use BackendBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;

class UserController extends Controller{

    public function newAction(Request $request){
        $helpers = $this->get('app.helpers');
        $json = $request->get('json',null);
        $params = json_decode($json);
	    $data = array("status" => "error", "code" => 400, "msg" => "User not created");

        if(!is_null($json)){
			$createdAt = new \DateTime("now");
			$image = null;
			$role = "user";
			$email = (isset($params->email)) ? $params->email : null;
	        $name = (isset($params->name)) && ctype_alpha($params->name) ? $params->name : null;
	        $surname = (isset($params->surname)) && ctype_alpha($params->surname)  ? $params->surname : null;
	        $password = (isset($params->password)) ? $params->password : null;

	        $emailConstraint = new Email();
	        $emailConstraint->message = "Email format not valid!!";
	        $validate_email = $this->get('validator')->validate($email, $emailConstraint);

	        if (count($validate_email) == 0 && !is_null($email) && !is_null($password) && !is_null($name) && !is_null($surname)){
				$user = new User();
				$user->setCreatedAt($createdAt);
				$user->setImage($image);
				$user->setRole($role);
				$user->setEmail($email);
				$user->setName($name);
				$user->setSurname($surname);
				$user->setPassword($password);

		        $em = $this->getDoctrine()->getManager();
				$isset_user = $em->getRepository('BackendBundle:User')->findBy(array("email" => $email));
				if(count($isset_user) == 0){
					$em->persist($user);
					$flush = $em->flush();
					$data = array("status" => "success", "code" => 200, "msg" => "New User created!!!");
				}else{
					$data = array("status" => "error", "code" => 400, "msg" => "User not created, duplicated");
				}
	        }
        }
        return $helpers->json($data);
    }
}
