<?php

namespace AppBundle\Services;
use Firebase\JWT\JWT;

class JwtAuth {

	public $mananger;

	public function __construct($manager) {
		$this->mananger = $manager;
	}

	public function signup($email, $password, $getHash = null){
		$key = "clave-secreta";
		$user = $this->mananger->getRepository('BackendBundle:User')->findOneBy(
			array(
				"email" => $email,
				"password" => $password
			)
		);
		$signup = false;
		if(is_object($user)){
			$signup = true;
		}

		if($signup){
			return array("status" => "success", "data", "Login success!!");
		}else{
			return array("status" => "error", "data", "Login failed!!");
		}
	}
}