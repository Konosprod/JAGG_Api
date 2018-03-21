<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require_once "../vendor/autoload.php";
require_once "handlers/exception.php";
require_once "controllers/apicontroller.php";
require_once "controllers/mapscontroller.php";
require_once "controllers/tagscontroller.php";
require_once "controllers/authorscontroller.php";
require_once "controllers/steamcontroller.php";
require_once "middleware/authmiddleware.php";


$config = include("config.php");

$app = new Slim\App(["settings" => $config]);

$container = $app->getContainer();

$container["upload_directory"] = __DIR__."/../maps/";
$container["thumbs_directory"] = __DIR__."/../thumbs/";
$container["download_base_url"] = "https://jagg.konosprod.fr/maps/";

$container["logger"] = function($c) {
	$logger = new \Monolog\Logger("jagg_api");
	$file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
	$logger->pushHandler($file_handler);
	return $logger;
};

$container["session"] = function($c) {
	return new \SlimSession\Helper;
};

$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container["settings"]["db"]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$capsule->getContainer()->singleton(
	IlluminateContractsDebugExceptionHandler::class
);

$container["db"] = function ($container) use ($capsule) {
	return $capsule;
};

// Everything related to api
$app->get("/", ApiController::class.":get");

$app->post("/auth", SteamController::class.":auth")->setName("auth");
$app->get("/auth", SteamController::class.":isAuth")->setName("isAuth");

$app->get("/maps", MapController::class.":listMaps");
$app->post("/maps", MapController::class.":createMap");
$app->get("/maps/search/{terms}", MapController::class.":searchMap");

$app->get("/maps/{mapid}", MapController::class.":getMap");
$app->put("/maps/{mapid}", MapController::class.":updateMap");
$app->delete("/maps/{mapid}", MapController::class.":deleteMap");

$app->get("/maps/{mapid}/download", MapController::class.":downloadMap");

$app->get("/tags", TagController::class.":listTags");
$app->post("/tags", TagController::class.":createTag");

$app->get("/tags/{tag}", TagController::class.":getTag");
$app->delete("/tags/{tagid}", TagController::class.":deleteTag");

$app->post("/authors", AuthorController::class.":createAuthor");

$app->get("/authors/{name}", AuthorController::class.":getAuthor");


$app->add(new AuthenticationMiddleware($container));

$app->add(new \Slim\Middleware\Session([
        "name"=>"session",
        "autorefresh" => true,
        "lifetime" => "1 hour"
]));


$app->run();
