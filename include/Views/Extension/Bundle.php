<?php
namespace MerapiPanel\Views\Extension;

use MerapiPanel\Utility\Http\Request;
use MerapiPanel\Views\Abstract\Extension;

class Bundle extends Extension
{

    public function fl_url(mixed $path, array $pattern = [])
    {

        $parse = parse_url($path ?? "");
        if (!isset($parse['scheme'])) {
            $parse['scheme'] = $_SERVER['REQUEST_SCHEME'];
        }
        if (!isset($parse['host'])) {
            $parse['host'] = $_SERVER['HTTP_HOST'];
        }

        $result_path = $parse['path'];
        preg_match_all('/\:([a-z0-9]+)/i', $result_path, $matches);


        if (isset($matches[1])) {
            foreach ($matches[1] as $value) {
                $result_path = preg_replace('/\:' . $value . '/', (isset($pattern[$value]) && !empty($pattern[$value]) ? $pattern[$value] : ""), $result_path);
            }
        }
        return $parse['scheme'] . "://" . $parse['host'] . "/" . ltrim($result_path, "/");
    }


    public function fn_time()
    {
        return time();
    }





    function fl_access_path(string $path)
    {

        if (!isset($_ENV["__MP_ACCESS__"], $_ENV["__MP_" . strtoupper($_ENV["__MP_ACCESS__"]) . "__"])) {
            return $path;
        }
        $access = $_ENV["__MP_" . strtoupper($_ENV["__MP_ACCESS__"]) . "__"];
        $prefix = $access["prefix"];

        return rtrim($prefix, "/") . "/" . ltrim($path, "/");
    }



    function fl_preg_replace($pattern, $replacement, $subject)
    {
        return preg_replace($pattern, $replacement, $subject);
    }


    /**
     * Get block content from the context
     * @option needs_context true
     */
    public function fn_block_content(array $context, string $blockName)
    {
        // Get block content from the context
        $blockContent = $context['_view']->renderBlock($blockName);

        return $blockContent;
    }
}