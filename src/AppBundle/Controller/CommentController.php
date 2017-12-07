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

	public function deleteAction(Request $request, $id = null){
		$helpers = $this->get('app.helpers');
		$hash = $request->get('authorization',null);
		$authCheck = $helpers->authCheck($hash);
		if($authCheck){
			$identity = $helpers->authCheck($hash,true);
			$user_id = (isset($identity->sub)) ? $identity->sub : null;
			$em = $this->getDoctrine()->getManager();
			$comment = $em->getRepository('BackendBundle:Comment')->findOneBy(array("id"=>$id));
			if(is_object($comment) && !is_null($user_id)){
				if($user_id == $comment->getUser()->getId() || $user_id == $comment->getVideo()->getUser()->getId()){
					$em->remove($comment);
					$em->flush();
					$data  = array( "status" => "success", "code" => 200, "msg" => "Comment deleted!!!" );
				}else{
					$data = array("status" => "error", "code" => 400, "msg" => "Comment not deleted!!!");
				}
			}else{
				$data = array("status" => "error", "code" => 400, "msg" => "Comment not deleted!!!");
			}
		}else{
			$data = array("status" => "error", "code" => 400, "msg" => "Authorization not valid!!!");
		}
		return $helpers->json($data);
	}

	public function listAction(Request $request, $id = null) {
		$helpers = $this->get('app.helpers');
		$em = $this->getDoctrine()->getManager();
		$video = $em->getRepository('BackendBundle:Video')->findOneBy(array("id"=>$id));
		$comments = $em->getRepository('BackendBundle:Comment')->findBy(array("video"=>$video), array('id'=>'DESC'));
		if(count($comments) >= 1){
			$data  = array( "status" => "success", "code" => 200, "msg" => "Comment deleted!!!", "data" => $comments);
		}else{
			$data = array("status" => "error", "code" => 400, "msg" => "video has no comments!!!");
		}
		return $helpers->json($data);
	}
}
