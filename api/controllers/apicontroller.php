<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Interop\Container\ContainerInterface as ContainerInterface;

class ApiController {

	protected $container;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public function get($request, $response, $args) {
		return $response->withStatus(200)->withJson(array("version" => "0.1"));
	}
}
