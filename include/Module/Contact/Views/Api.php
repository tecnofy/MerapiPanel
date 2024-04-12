<?php
namespace MerapiPanel\Module\Contact\Views;

use MerapiPanel\Box\Module\__Fragment;
use MerapiPanel\Box\Module\Entity\Module;
use MerapiPanel\Utility\AES;

class Api extends __Fragment
{
	protected $module;
	function onCreate(Module $module)
	{
		$this->module = $module;
	}

	public function wa($notelp, $message)
	{

		$link = "/redirect/contact/{type}/{data}";
		$link = str_replace('{type}', "wa", $link);
		$link = str_replace('{data}', AES::encrypt("phone=$notelp&text=" . urlencode($message)), $link);
		// return "https://api.whatsapp.com/send?phone=$notelp&text=" . urlencode($message);

		return $link;
		// DB::table("contact")->insert([])->execute();
	}


}