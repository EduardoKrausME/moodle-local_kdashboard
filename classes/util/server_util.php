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
 * server_util file
 *
 * introduced 13/05/17 14:27
 *
 * @package   local_kdashboard
 * @copyright 2017 Eduardo Kraus {@link http://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kdashboard\util;

/**
 * Class server_util
 *
 * @package local_kdashboard\util
 */
class server_util {
    /**
     * Function get_kpathath
     *
     * @param bool $create
     *
     * @return string
     */
    public static function get_kpathath($create = true) {
        global $CFG;

        if ($create) {
            @mkdir("{$CFG->dataroot}/kopere");
            @mkdir("{$CFG->dataroot}/kopere/dashboard");
        }

        return "{$CFG->dataroot}/kopere/dashboard/";
    }

    /**
     * Function function_enable
     *
     * @param $function
     *
     * @return bool
     */
    public static function function_enable($function) {
        return is_callable($function) && false === stripos(ini_get("disable_functions"), $function);
    }
}
