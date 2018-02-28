<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require "../vendor/autoload.php";
require "handlers/exception.php";
require "controllers/apicontroller.php";
require "controllers/mapscontroller.php";
require "controllers/tagscontroller.php";
require "controllers/authorscontroller.php";

$config = include("config.php");

$app = new Slim\App(["settings" => $config]);

$container = $app->getContainer();
$container["upload_directory"] = __DIR__."/maps";

$container["logger"] = function($c) {
    $logger = new \Monolog\Logger("jagg_api");
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};


$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container["settings"]["db"]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$capsule->getContainer()->singleton(
	IlluminateContractsDebugExceptionHandler::class
);


// Everything related to api
$app->get("/", ApiController::class.":get");




$app->get("/maps", MapController::class.":listMaps");
$app->post("/maps", MapController::class.":createMap");

$app->get("/maps/{mapid}", MapController::class.":getMap");
$app->put("/maps/{mapid}", MapController::class.":updateMap");
$app->delete("/maps/{mapid}", MapController::class.":deleteMap");

$app->get("/tags", TagController::class.":listTags");
$app->post("/tags", TagController::class.":createTag");

$app->get("/tags/{tag}", TagController::class.":getTag");
$app->delete("/tags/{tag}", TagController::class.":deleteTag");

$app->post("/authors", AuthorController::class.":createAuthor");

$app->get("/authors/{steamid}", AuthorController::class.":getAuthor");

$app->run();
