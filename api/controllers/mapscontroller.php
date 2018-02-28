<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Interop\Container\ContainerInterface as ContainerInterface;

require "models/map.php";

class MapController {

	protected $container;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	/**
	* List maps from the store
	* @route : /maps
	*/
	public function listMaps($request, $response, $args) {
	        return $response->getBody()->write(Map::paginate(3));
	}


	/**
	* Get map informations
	* @route : /maps/{mapid}
	*/
	public function getMap($request, $response, $args) {
	        $id = $args["mapid"];
        	$map = Map::find($id);
        	$ret = null;

        	if(!is_null($map)) {

                	$ret = $response->withStatus(200)
                        	->withHeader("Content-Type", "application/json")
                        	->write($map->toJson());
        	} else {
                	$ret = $response->withStatus(404)
                        	        ->withJson(array("message"=>"Not found"));
        	}
        	return $ret;
	}

	/**
	* Create a map
	* @route: /maps
	*/
	public function createMap($request, $response, $args) {

	        $data = $response->getParsedBody();
        	$map = new Map();
	}

	/**
	* Update map
	* @route : /maps/{mapid}
	*/
	public function updateMap($request, $response, $args) {
		return $response->getBody()->write("update ".$args["mapid"]);
	}

	/**
	* Update a map
	* @route: /maps/{mapid}
	*/
	public function deleteMap($request, $response, $args) {
		return $response->getBody()->write("delete ".$args["mapid"]);
	}


}
