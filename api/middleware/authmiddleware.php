<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Interop\Container\ContainerInterface as ContainerInterface;

class AuthenticationMiddleware {
	private $DEBUG = true;
	protected $container;
	private $freeRoutes = array("isAuth", "auth");

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public function __invoke($request, $response, $next) {
		if($this->DEBUG == true) {
			return $next($request, $response);
		} else {
			$route = $request->getAttribute("route");

			if(in_array($route->getName(), $this->freeRoutes)) {
				return $next($request, $response);
			} else {
				if($this->container["session"]->auth == true) {
					return $next($request, $response);
				} else {
                        		return $response->withStatus(403)
                        	        	        ->withJson(array("auth"=> false));
				}
			}
		}
	}
}
