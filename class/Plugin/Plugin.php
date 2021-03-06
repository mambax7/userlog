<?php

namespace XoopsModules\Userlog\Plugin;

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author          trabis <lusopoemas@gmail.com>
 */

use XoopsModules\Userlog;

// irmtfan copy from xoops26 xoops_lib/Xoops/Module/plugin.php class
// change XoopsLoad -> self
// change $xoops -> $GLOBALS['xoops']
// change  Userlog\PluginAbstract , Plugin
// change  $xoops->getActiveModules() -> xoops_getActiveModules()

class Plugin
{
    /**
     * @param string $dirname
     * @param string $pluginName
     * @param bool   $force
     *
     * @return bool|Userlog\Plugin\PluginAbstract false if plugin does not exist
     */
    public static function getPlugin($dirname, $pluginName = 'system', $force = false)
    {
        $inactiveModules = false;
        if ($force) {
            $inactiveModules = [$dirname];
        }
        $available = self::getPlugins($pluginName, $inactiveModules);
        if (!in_array($dirname, array_keys($available))) {
            return false;
        }

        return $available[$dirname];
    }

    /**
     * @param string     $pluginName
     * @param array|bool $inactiveModules
     *
     * @return mixed
     */
    public static function getPlugins($pluginName = 'system', $inactiveModules = false)
    {
        static $plugins = [];
        if (!isset($plugins[$pluginName])) {
            $plugins[$pluginName] = [];
            //$xoops = \Xoops::getInstance();

            //Load interface for this plugin
            if (!self::loadFile($GLOBALS['xoops']->path("modules/{$pluginName}/class/plugin/interface.php"))) {
                return $plugins[$pluginName];
            }

            $dirnames = xoops_getActiveModules();
            if (is_array($inactiveModules)) {
                $dirnames = array_merge($dirnames, $inactiveModules);
            }
            foreach ($dirnames as $dirname) {
                if (self::loadFile($GLOBALS['xoops']->path("modules/{$dirname}/class/plugin/{$pluginName}.php"))
                    || self::loadFile($GLOBALS['xoops']->path("modules/{$pluginName}/class/plugin/{$dirname}.php"))) {
                    $className = ucfirst($dirname) . ucfirst($pluginName) . 'Plugin';
                    $interface = ucfirst($pluginName) . 'PluginInterface';
                    $class     = new $className($dirname);
                    if ($class instanceof Userlog\Plugin\PluginAbstract && $class instanceof $interface) {
                        $plugins[$pluginName][$dirname] = $class;
                    }
                }
            }
        }

        return $plugins[$pluginName];
    }

    /**
     * @param      $file
     * @param bool $once
     *
     * @return bool
     */
    public static function loadFile($file, $once = true)
    {
        self::_securityCheck($file);
        if (self::fileExists($file)) {
            if ($once) {
                require_once $file;
            } else {
                require_once $file;
            }

            return true;
        }

        return false;
    }

    /**
     * @param $file
     *
     * @return mixed
     */
    public static function fileExists($file)
    {
        static $included = [];
        if (!isset($included[$file])) {
            $included[$file] = file_exists($file);
        }

        return $included[$file];
    }

    /**
     * @param $filename
     */
    protected static function _securityCheck($filename)
    {
        /**
         * Security check
         */
        if (preg_match('/[^a-z0-9\\/\\\\_.:-]/i', $filename)) {
            exit('Security check: Illegal character in filename');
        }
    }
}
