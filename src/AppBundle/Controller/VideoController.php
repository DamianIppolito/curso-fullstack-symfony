<?php

namespace AppBundle\Controller;

use BackendBundle\BackendBundle;
use BackendBundle\Entity\Video;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class VideoController extends Controller{
    public function newAction(Request $request){
	    $helpers = $this->get('app.helpers');
	    $hash = $request->get('authorization',null);
	    $auth_check = $helpers->authCheck($hash);
	    if($auth_check){
		    $identity = $helpers->authCheck($hash, true);
		    $json = $request->get('json',null);
		    if(!is_null($json)){
			    $params = json_decode($json);
			    $createdAt = new \DateTime( "now" );
			    $updatedAt = new \DateTime( "now" );
			    $image = null;
			    $video_path = null;
			    $user_id = (!is_null($identity->sub)) ? $identity->sub : null;
			    $title = (isset($params->title)) ? $params->title : null;
			    $description = (isset($params->description)) ? $params->description : null;
			    $status = (isset($params->status)) ? $params->status : null;
			    if(!is_null($user_id) && !is_null($title)){
				    $em = $this->getDoctrine()->getManager();
				    $user = $em->getRepository('BackendBundle:User')->findOneBy(array( "id" => $user_id));

				    $video = new Video();
				    $video->setUser($user);
				    $video->setCreatedAt($createdAt);
				    $video->setStatus($status);
				    $video->setTitle($title);
				    $video->setDescription($description);
				    $video->setUpdatedAt($updatedAt);

				    $em->persist($video);
				    $em->flush();

				    $video = $em->getRepository("BackendBundle:Video")->findOneBy(array(
				    	"user" => $user,
					    "title" => $title,
					    "status" => $status,
					    "createdAt" => $createdAt
				    ));

				    $data  = array( "status" => "success", "code" => 200, "data" => $video );
			    }else{
				    $data = array("status" => "error", "code" => 400, "msg" => "Video not created!!!");
			    }
		    }else{
			    $data = array("status" => "error", "code" => 400, "msg" => "Video not created, params failed!!!");
		    }
	    }else{
		    $data = array("status" => "error", "code" => 400, "msg" => "Authorization not valid!!!");
	    }
	    return $helpers->json($data);
    }

	public function editAction(Request $request, $id = null){
		$helpers = $this->get('app.helpers');
		$hash = $request->get('authorization',null);
		$auth_check = $helpers->authCheck($hash);
		if($auth_check){
			$identity = $helpers->authCheck($hash, true);
			$json = $request->get('json',null);
			if(!is_null($json)){
				$params = json_decode($json);
				$updatedAt = new \DateTime( "now" );
				$image = null;
				$video_path = null;
				$user_id = (!is_null($identity->sub)) ? $identity->sub : null;
				$title = (isset($params->title)) ? $params->title : null;
				$description = (isset($params->description)) ? $params->description : null;
				$status = (isset($params->status)) ? $params->status : null;
				if(!is_null($user_id) && !is_null($title)){
					$em = $this->getDoctrine()->getManager();
					$video = $em->getRepository( "BackendBundle:Video" )->findOneBy( array( "id" => $id ) );
					if(isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {
						$video->setStatus( $status );
						$video->setTitle( $title );
						$video->setDescription( $description );
						$video->setUpdatedAt( $updatedAt );

						$em->persist( $video );
						$em->flush();

						$data = array( "status" => "success", "code" => 200, "data" => 'Video updated!!!' );
					}else{
						$data = array("status" => "error", "code" => 400, "msg" => "Video update error, you not owner!!!");
					}
				}else{
					$data = array("status" => "error", "code" => 400, "msg" => "Video not updated!!!");
				}
			}else{
				$data = array("status" => "error", "code" => 400, "msg" => "Video not updated, params failed!!!");
			}
		}else{
			$data = array("status" => "error", "code" => 400, "msg" => "Authorization not valid!!!");
		}
		return $helpers->json($data);
	}
}
