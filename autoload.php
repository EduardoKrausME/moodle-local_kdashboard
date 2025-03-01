<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Autoload file
 *
 * introduced 23/05/17 17:59
 *
 * @package   local_kdashboard
 * @copyright 2017 Eduardo Kraus {@link http://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . "/locallib.php");

spl_autoload_register("kdashboard_autoload");
/**
 * Function kdashboard_autoload
 *
 * @param $classname
 */
function kdashboard_autoload($classname) {
    global $CFG;

    if (strpos($classname, "kopere") === false) {
        return;
    }

    $classname = str_replace('\\', '/', $classname);
    preg_match("/local_(.*?)\/(.*)/", $classname, $classpartes);
    if (isset($classpartes[2])) {
        $file = "{$CFG->dirroot}/local/{$classpartes[1]}/classes/{$classpartes[2]}.php";
        if (file_exists($file)) {
            require_once($file);
        }
    }
}

/**
 * Function kdashboard_load_class
 *
 * @throws coding_exception
 */
function kdashboard_load_class() {
    $classname = optional_param("classname", false, PARAM_TEXT);
    $method = optional_param("method", "", PARAM_TEXT);
    if (!$classname) {
        $classname = "dashboard";
        $method = "start";
    }

    if (strpos($classname, "-")) {
        $class = "\\local_k" . str_replace("-", '\\', $classname);
    } else {
        $class = "\\local_kdashboard\\{$classname}";
    }

    $class = str_replace("?", "", $class);

    $instance = new $class();
    $instance->$method();
}

/**
 * Function get_path_query
 *
 * @return string
 * @throws coding_exception
 */
function get_path_query() {
    $classname = optional_param("classname", "", PARAM_TEXT);
    $method = optional_param("method", "", PARAM_TEXT);
    return "{$classname}-{$method}";
}

/**
 * Function get_string_kopere
 *
 * @param $identifier
 * @param null $object
 *
 * @return string
 * @throws coding_exception
 */
function get_string_kopere($identifier, $object = null) {
    return get_string($identifier, "local_kdashboard", $object);
}

// Alias this class to the old name.
// In future all uses of this class will be corrected and the legacy references will be removed.
class_alias( \local_kdashboard\util\message::class, "local_kdashboard\\util\\mensagem");
