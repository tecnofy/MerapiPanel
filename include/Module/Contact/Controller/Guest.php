<?php
namespace MerapiPanel\Module\Contact\Controller;

use MerapiPanel\Box\Module\__Controller;
use MerapiPanel\Utility\AES;
use MerapiPanel\Utility\Http\Request;
use MerapiPanel\Utility\Http\Response;
use MerapiPanel\Views\View;
use MerapiPanel\Utility\Router;

class Guest extends __Controller
{

	function register()
	{
		Router::GET("/redirect/contact/{type}/{data}", "contact", self::class);
		// register other route
	}
	function contact(Request $req, Response $response)
	{

		$type = $req->type();
		$data = AES::decrypt($req->data());

		if ($type == "wa" && !empty($data)) {
			$response->setHeader("Location", "https://api.whatsapp.com/send?" . $data);
			return $response;
		}

		return View::render("index.html.twig");
	}
}