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

	public function editAction(Request $request, Video $video = null){
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
					if(!is_null($video) && isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {
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

	public function uploadAction(Request $request, Video $video = null){
		$helpers = $this->get('app.helpers');
		$hash = $request->get('authorization',null);
		$auth_check = $helpers->authCheck($hash);
		if($auth_check) {
			$identity = $helpers->authCheck( $hash, true );
			$em = $this->getDoctrine()->getManager();
			if(!is_null($video) && isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {
				$file_image = $request->files->get('image',null);
				$file_video = $request->files->get('video',null);
				if(!empty($file_image) && !is_null($file_image)){
					$ext = $file_image->guessExtension();
					if($ext == 'jpeg' || $ext == 'jpg' || $ext == 'png' || $ext == 'gif'){
						$file_name = time().'.'.$ext;
						$path_of_file = 'uploads/video_images/video_'.$video->getId();
						$file_image->move($path_of_file, $file_name);
						$video->setImage($file_name);
						$data  = array( "status" => "success", "code" => 200, "msg" => "Video Image uploaded!!!" );
					}else{
						$data = array("status" => "error", "code" => 400, "msg" => "File format not valid");
					}
				}else{
					$ext = $file_video->guessExtension();
					if($ext == 'mp4' || $ext == 'avi'){
						$file_name = time().'.'.$ext;
						$path_of_file = 'uploads/video_files/video_'.$video->getId();
						$file_video->move($path_of_file, $file_name);
						$video->setVideoPath($file_name);
						$data  = array( "status" => "success", "code" => 200, "msg" => "Video File uploaded!!!" );
					}else{
						$data = array("status" => "error", "code" => 400, "msg" => "File format not valid");
					}
				}
				$em->persist($video);
				$em->flush();
			}else{
				$data = array("status" => "error", "code" => 400, "msg" => "Video update error, you not owner!!!");
			}

		}else{
			$data = array("status" => "error", "code" => 400, "msg" => "Authorization not valid!!!");
		}
		return $helpers->json($data);
	}

	public function videosAction(Request $request){
		$helpers = $this->get('app.helpers');
		$em = $this->getDoctrine()->getManager();
		$dql = "SELECT v FROM BackendBundle:Video v ORDER BY v.id DESC";
		$query = $em->createQuery($dql);
		$page = $request->query->getInt("page", 1);
		$paginator = $this->get('knp_paginator');
		$items_per_page = 6;
		$pagination = $paginator->paginate($query, $page, $items_per_page);
		$total_items_count = $pagination->getTotalItemCount();

		$data  = array(
			"status" => "success",
			"total_items_count" => $total_items_count,
			"page_actual" => $page,
			"items_per_page" =>  $items_per_page,
			"total_pages" => ceil($total_items_count / $items_per_page),
			"data" => $pagination
		);

		return $helpers->json($data);
	}
}
