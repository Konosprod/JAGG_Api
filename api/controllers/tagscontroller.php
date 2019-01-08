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
		$offset = 0;
		$queries = $request->getQueryParams();

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

                return $response->withStatus(200)
                                ->withHeader("Content-Type", "application/json")
                                ->write(Tag::orderBy($orderby, $sort)->offset($offset)->take(15)->get()->toJson());

	}

	/**
	* Create a tag
	* @route : /tags
	*/
	public function createTag($request, $response, $args) {
		$data = $request->getParsedBody();
		$this->container->get("logger")->addInfo("Tag created : ".var_export($data, true));
		$tag = Tag::where("tag", $data["tag"])->first();

		if(is_null($tag)) {
			$tag = new Tag();
			$tag->tag = $data["tag"];
			$tag->save();
		} else {
			return $response->withStatus(200)
					->withJson(array("message"=>"Already exist"));
		}
	}

	/**
	* Add tag to a map
	* @route /tags/{tag}/{mapid}
	*/
	public function addTag($request, $response, $args) {
		$data = $request->getParsedBody();
		$tag = Tag::where("tag", $args["tag"])->first();

		if(is_null($tag)) {
			$tag = new Tag();
			$tag->tag = trim($args["tag"]);
		}

		$map = Map::find($args["mapid"]);
		$tag->maps()->attach($map->id);
		$tag->save();


		$map->tags()->attach($tag->id);
		$tag->save();
		$map->save();

		return $response->withStatus(200)
				->withJson(array("message"=>"added"));
	}

	/**
	* Remove tag from map
	* @route /tags/{tag}/{mapid}
	*/
	public function detachTag($request, $response, $args) {
		$tag = Tag::where("tag", $args["tag"])->first();
		$map = Map::find($args["mapid"]);

		$tag->maps()->detach($map->id);
		$map->tags()->detach($tag->id);

		$tag->save();
		$map->save();

		return $response->withStatus(200)
				->withJson(array("message"=>"detached"));

	}

	/**
	* Get a tag
	* @route /tags/{tag}
	*/
	public function getTag($request, $response, $args) {
		$tag = Tag::where("tag", $args["tag"])->first();

		$queries = $request->getQueryParams();
		$offset = 0;

               if(isset($queries["offset"])) {
                        $offset = $queries["offset"];
                	$this->container["logger"]->addInfo($offset);
		}

                $orderby = "id";

                if(isset($queries["orderby"])) {
                        $orderby = $queries["orderby"];
                }

                $sort = "DESC";

                if(isset($queries["sort"])) {
                        $sort = $queries["sort"];
                }

		if(!is_null($tag)) {
			return $response->withStatus(200)
					->withHeader("Content-Type", "application/json")
					->write($tag->maps()->orderBy($orderby, $sort)->offset($offset)->take(15)->get()->toJson());
		} else {
			return $response->withStatus(200)
					->withJson(array());
		}
	}

	/**
	* Delete a tag
	* @route : /tags/{tagid}
	*/
	public function deleteTag($request, $response, $args) {
		$tag = Tag::find($args["tagid"]);

		if(!is_null($tag)) {
			$tag-delete();

			return $response->withStatus(200)
					->withJson(array("message"=>"Deleted"));
		} else {
			return $response->withStatus(404)
					->withJson(array("message"=>"Not found"));
		}
	}
}
