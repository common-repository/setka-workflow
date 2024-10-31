<?php
namespace Setka\Workflow\Services;

class ScriptsDebug
{
    public static function isDebug()
    {
        if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG == true) {
            return true;
        }

        return false;
    }
}
