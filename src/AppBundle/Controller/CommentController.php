<?php

namespace AppBundle\Controller;

use BackendBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CommentController extends Controller {
	public function newAction(Request $request){
		$helpers = $this->get('app.helpers');
		$hash = $request->get('authorization',null);
		$authCheck = $helpers->authCheck($hash);
		if($authCheck){
			$identity = $helpers->authCheck($hash,true);
			$json = $request->get('json',null);
			if(!is_null($json)){
				$params = json_decode($json);
				$createdAt = new \DateTime('now');
				$user_id = (isset($identity->sub)) ? $identity->sub : null;
				$video_id = (isset($params->video_id)) ? $params->video_id : null;
				$body = (isset($params->body)) ? $params->body : null;
				if(!is_null($user_id) && !is_null($video_id)){
					$em = $this->getDoctrine()->getManager();
					$user = $em->getRepository('BackendBundle:User')->findOneBy(array("id"=>$user_id));
					$video = $em->getRepository('BackendBundle:Video')->findOneBy(array("id"=>$video_id));
					$comment = new Comment();
					$comment->setUser($user);
					$comment->setVideo($video);
					$comment->setBody($body);
					$comment->setCreatedAt($createdAt);
					$em->persist($comment);
					$em->flush();
					$data  = array( "status" => "success", "code" => 200, "msg" => "Comment created!!!" );
				}else{
					$data = array("status" => "error", "code" => 400, "msg" => "Comment not created!!!");
				}
			}else{
				$data = array("status" => "error", "code" => 400, "msg" => "Params not valid!!!");
			}
		}else{
			$data = array("status" => "error", "code" => 400, "msg" => "Authorization not valid!!!");
		}
		return $helpers->json($data);
	}
}
