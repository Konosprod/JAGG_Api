<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Interop\Container\ContainerInterface as ContainerInterface;

use GuzzleHttp\Client;
use Steam\Configuration;
use Steam\Runner\GuzzleRunner;
use Steam\Runner\DecodeJsonStringRunner;
use Steam\Steam;
use Steam\Utility\GuzzleUrlBuilder;

class SteamController {

	private $APP_ID = 480;
	private $steam = null;
	protected $container = null;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
		$this->steam = new Steam(new Configuration([
			Configuration::STEAM_KEY => '3DA0241A93565468FBA7778392F234BC'
		]));

		$this->steam->addRunner(new GuzzleRunner(new Client(), new GuzzleUrlBuilder()));
		$this->steam->addRunner(new DecodeJsonStringRunner());

	}

	public function isAuth($request, $response, $args) {
		if($this->container["session"]["auth"]) {
			return $response->withStatus(200)
					->withJson(array("auth"=>true));
		} else {
			return $response->withStatus(200)
					->withJson(array("auth"=>false));
		}
	}

	public function auth($request, $response, $args) {
		$data = $request->getParsedBody();
		$steamid = $data["steamid"];
		$ticket = $data["ticket"];

		$this->container->get("session");

		$result = $this->steam->run(new \Steam\Command\UserAuth\AuthenticateUserTicket($this->APP_ID, $ticket));

		$steamres = (array) $result["response"]["params"];

		if($steamres["steamid"] == $steamid) {
			$this->container["session"]["auth"] = true;
			return $response->withJson(array("auth" => true));
		} else {
			$this->container["session"]["auth"] = false;
			return $response->withStatus(403)
					->write(array("auth"=> false));
		}
	}

}
