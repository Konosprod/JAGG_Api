<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Interop\Container\ContainerInterface as ContainerInterface;

require "models/tag.php";

class TagController {
	protected $container;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	/**
	* List available tags
	* @route! /tags
	*/
	public function listTags($request, $response, $args) {
		return $response->getBody()->write("list tags");
	}

	/**
	* Create a tag
	* @route : /tags
	*/
	public function createTag($request, $response, $args) {
		return $response->getBody()->write("create tag");
	}

	/**
	* Get a tag
	* @route /tags/{tag}
	*/
	public function getTag($request, $response, $args) {
		return $response->getBody()->write("get ".$args["tag"]);
	}

	/**
	* Delete a tag
	* @route : /tags/{tag}
	*/
	public function deleteTag($request, $response, $args) {
		return $response->getBody()->write("delete ".$args["tag"]);
	}
}
