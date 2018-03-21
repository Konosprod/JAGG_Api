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
	* Get maps from author
	* @route : /authors/{name}
	*/
	public function getAuthor($request, $response, $args) {
		$searchTerm = $args["name"];
                $queries = $request->getQueryParams();
                $offset = 0;

               if(isset($queries["offset"])) {
                        $offset = $queries["offset"];
                }

                $orderby = "id";

                if(isset($queries["orderby"])) {
                        $orderby = $queries["orderby"];
                }

                $sort = "DESC";

                if(isset($queries["sort"])) {
                        $sort = $queries["sort"];
                }

		$res = Map::whereHas('author', function($query) use ($searchTerm) {
			$query->where("authors.name", "like", "%".$searchTerm."%");
		});

		return $response->withStatus(200)
				->withHeader("Content-Type", "application/json")
				->write($res->orderBy($orderby, $sort)->offset($offset)->take(15)->get()->toJson());
	}
}
