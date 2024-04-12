<?php
namespace MerapiPanel\Box\Module;

use Closure;
use MerapiPanel\Box\Module\Entity\Module;
use MerapiPanel\Utility\Http\Request;

abstract class __Middleware extends __Fragment
{
    protected Module $module;
    public function onCreate(Module $module)
    {
        $this->module = $module;
    }

    abstract function handle(Request $request, Closure $next);
}