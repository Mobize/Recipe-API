<?php

namespace App\Service;

class Helpers{

	public function json($data){
		$normalizers = array(new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer());
		$encoders = array("json" => new \Symfony\Component\Serializer\Encoder\JsonEncoder());

		$serializer = new \Symfony\Component\Serializer\Serializer($normalizers, $encoders);
		$json = $serializer->serialize($data, 'json');

		$response = new \Symfony\Component\HttpFoundation\Response();
		$response->setContent($json);
		$response->headers->set('Content-Type','application/json');

		return $response;
	}
}

