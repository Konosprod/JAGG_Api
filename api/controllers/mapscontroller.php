<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Interop\Container\ContainerInterface as ContainerInterface;
use \Illuminate\Support\Carbon as Carbon;

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
				->write(Map::orderBy($orderby, $sort)->offset($offset)->take(15)->get()->toJson());
	}

	/**
	* Download a map file
	* @route : /maps/{mapid}/download
	*/
	public function downloadMap($request, $response, $args) {
		$id = $args["mapid"];

		$map = Map::find($id);

		if(isset($map)) {
			$download_count = $map->download_count;
			$download_count = $download_count + 1;

			$monthly = $map->monthly_download_count;
			$monthly = $monthly + 1;

			$map->monthly_download_count = $monthly;
			$map->download_count = $download_count;

			$map->save();

			return $response->withStatus(302)->withHeader("Location", $map->path);


		} else {
			return $response->withStatus(404)
					->withJson(array("message"=>"Not found"));
		}
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
	        $data = $request->getParsedBody();
        	$map = new Map();

		$files = $request->getUploadedFiles();
		$this->container["logger"]->addInfo(json_encode(explode(",", $data["tags"])));


		if(!empty($files["map"]) && !empty($files["thumb"])) {
			if($files["map"]->getError() === 0 && $files["thumb"]->getError() === 0) {
				$filename = $files["map"]->getClientFilename();

				$map->download_count = 0;
				$map->monthly_download_count = 0;
				$map->name = pathinfo($filename)["filename"];


				$author = Author::where("steamid", $data["steamid"])->first();

				if(is_null($author)) {
					$author = new Author();
					$author->steamid = $data["steamid"];
					$author->name = $data["name"];
					$author->save();
				}
					$map->author_id = $author->id;

				$map->save();
				$this->container->get("logger")->addInfo("Map ".$map->id." created at ".$map->created_at->timestamp);

				$path = $this->container->get("upload_directory").$map->id."_".$filename;
				$files["map"]->moveTo($path);
				$map->path = $this->container->get("download_base_url").$map->id."_".$filename;

				$map->save();

				$thumbPath = $this->container->get("thumbs_directory").$map->id.".png";
				$files["thumb"]->moveTo($thumbPath);

				$tags = array_filter(explode(",", $data["tags"]));

				foreach($tags as $createTag) {

					$this->container->get("logger")->addInfo($createTag);

					$tag = Tag::where("tag", $createTag)->first();

					if(is_null($tag)) {
						$newTag = new Tag();
						$newTag->tag = trim($createTag);
						$newTag->maps()->attach($map->id);
						$newTag->save();

						$map->tags()->attach($newTag->id);
						$newTag->save();
						$map->save();

						$this->container->get("logger")->addInfo("Tag \"".$newTag->tag."\" created");

					} else {
						$map->tags()->attach($tag->id);
						$map->save();
						$tag->save();
					}
				}

				$map->last_update = Carbon::now('UTC');
				$map->save();

				return $response->withStatus(200)
						->withHeader("Content-Type", "application/json")
						->write(Map::Find($map->id)->toJson());
			}
		} else {
			return $response->withStatus(400)
					->withJson(array("message"=>"Empty mapfile"));
		}
	}

	/**
	* Update map
	* @route : /maps/{mapid}
	*/
	public function updateMap($request, $response, $args) {
		$mapid = $args["mapid"];

		$map = Map::find($mapid);

		if(!is_null($map)) {

			$files = $request->getUploadedFiles();
			$data = $request->getParsedBody();

			if(!empty($files["map"])) {
				if($files["map"]->getError() === 0) {
					$path = $this->container->get("upload_directory").$map->id."_".$files["map"]->getClientFilename();
					$files["map"]->moveTo($path);


					$this->container->get("logger")->addInfo($map->created_at->timestamp);
                                	$tags = array_filter(explode(",", $data["tags"]));

                                	foreach($tags as $createTag) {

                                        	$this->container->get("logger")->addInfo($createTag);

                                        	$tag = Tag::where("tag", $createTag)->first();

                                        	if(is_null($tag)) {
                                                	$newTag = new Tag();
                                                	$newTag->tag = trim($createTag);
                                                	$newTag->maps()->attach($map->id);
                                                	$newTag->save();

                                                	$map->tags()->attach($newTag->id);
                                                	$newTag->save();
                                                	$map->save();

							$this->container->get("logger")->addInfo("Tag \"".$newTag->tag."\" created");

                                        	} else {
                                                	$map->tags()->attach($tag->id);
                                                	$map->save();
                                                	$tag->save();
                                        	}
                                	}

					$map->last_update = Carbon::now('UTC');

					$map->touch();
					$map->save();

					$this->container->get("logger")->addInfo("Map ".$map->id." updated at ".$map->last_update->timestamp);

					if(!empty($files["thumb"])) {
						$thumbPath = $this->container->get("thumbs_directory").$map->id.".png";
						$files["thumb"]->moveTo($thumbPath);
					} else {
						$this->container->get("logger")->addInfo("No thumbs");
					}

					return $response->withStatus(200)
							->withHeader("Content-Type", "application/json")
							->write(Map::Find($map->id)->toJson());
				}
			}

		} else {
			return $response->withStatus(404)
					->withJson(array("message"=>"Not found"));
		}

		//return $response->getBody()->write("update ".$args["mapid"]);
	}

	/**
	* Delete a map
	* @route: /maps/{mapid}
	*/
	public function deleteMap($request, $response, $args) {
		$mapid = $args["mapid"];

		$map = Map::find($mapid);

		if(!is_null($map)) {
			$filename = str_replace($this->container->get("download_base_url"), "", $map->path);
			$path = $this->container->get("upload_directory").$filename;

			unlink($path);

			$map->delete();

			return $response->withStatus(200)
					->withJson(array("message"=>"Deleted"));
		} else {
			return $response->withStatus(404)
					->withJson(array("message"=>"Not found"));
		}
	}

	public function searchMap($request, $response, $args) {
		$search = $args["terms"];

                $offset = 0;
                $queries = $request->getQueryParams();

                if(isset($queries["offset"])) {
                        $offset = $queries["offset"];
                }


		return $response->withStatus(200)
				->withHeader("Content-Type", "application/json")
				->write(Map::where("name", "LIKE", "%".$search."%")->orderBy("id", "DESC")->offset($offset)->take(15)->get()->toJson());
	}


}
