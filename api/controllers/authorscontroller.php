<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Interop\Container\ContainerInterface as ContainerInterface;

require "models/author.php";

class AuthorController {
	protected $container;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	/**
	* Create a new author
	* @route : /authors
	*/
	public function createAuthor($request, $response, $args) {
		return $response->getBody()->write("create author");
	}

	/**
	* Get an author
	* @route : /authors/{steamid}
	*/
	public function getAuthor($resquest, $response, $args) {
		return $response->getBody()->write("get ".$args["steamid"]);
	}
}
