<?php
namespace MerapiPanel\Module\FileManager\Views\Admin;

use MerapiPanel\Box\Module\__Fragment;
use MerapiPanel\Box\Module\Entity\Module;
use MerapiPanel\Utility\Http\Request;
use Symfony\Component\Filesystem\Path;

class Api extends __Fragment
{

    protected Module $module;
    protected string $root;

    function onCreate(Module $module)
    {

        $this->module = $module;
        $this->root = Path::join($_ENV['__MP_CWD__'], "content");
    }

    public function fetch()
    {

        $root = $this->root;

        $request = Request::getInstance();
        if ($request->d) {
            $root = Path::join($root, "$request->d");
        }

        return array_filter(scandir($root, SCANDIR_SORT_DESCENDING), function ($file) {
            return !in_array($file, ['.', '..']);
        });

    }


    public function is_file($file)
    {
        $root = $this->root;
        if(Request::getInstance()->d) {
            $root = Path::join($root, Request::getInstance()->d);
        }
        if (is_string($file)) {
            $file = Path::join($root, $file);

            if (is_file($file)) {
                return true;
            }
        }
        return false;
    }




    function getParent()
    {
        $request = Request::getInstance();
        if ($request->d) {
            return $request->d;
        }
        return "/";
    }


    public function getArrayParent()
    {

        $root_array = array_values(array_filter(explode("/", $this->getParent())));

        return array_merge(["/"], $root_array);
    }





    public function state_is_file()
    {
        $parent = $this->getParent();
        $full_path = Path::join($this->root, $parent);
        return is_file($full_path);
    }

    public function state_path()
    {
        $parent = $this->getParent();
        $full_path = Path::join($this->root, $parent);
        return str_replace(str_replace("\\", "/", $_ENV['__MP_CWD__']), "", $full_path);
    }
}