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
 * local_kdashboard_menu file
 *
 * @package   local_kdashboard
 * @copyright 2017 Eduardo Kraus {@link http://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kdashboard\vo;

/**
 * Class local_kdashboard_menu
 *
 * @package local_kdashboard\vo
 */
class local_kdashboard_menu extends \stdClass {

    /** @var int */
    public $id;

    /** @var string */
    public $link;

    /** @var string */
    public $title;

    /** @var int */
    public $menuid;

    /** @var int */
    public $inheader;

    /** @var string */
    public $icon;

    /**
     * Function create_by_object
     *
     * @param $item
     *
     * @return local_kdashboard_menu
     * @throws \coding_exception
     */
    public static function create_by_object($item) {
        $return = new local_kdashboard_menu();

        $return->id = $item->id;
        $return->link = optional_param("link", $item->link, PARAM_TEXT);
        $return->title = optional_param("title", $item->title, PARAM_TEXT);
        $return->menuid = optional_param("menuid", $item->menuid, PARAM_INT);
        $return->inheader = optional_param("inheader", $item->inheader, PARAM_INT);
        $return->icon = optional_param("icon", $item->icon, PARAM_TEXT);

        return $return;
    }

    /**
     * Function create_by_default
     *
     * @return local_kdashboard_menu
     * @throws \coding_exception
     */
    public static function create_by_default() {
        $return = new local_kdashboard_menu();

        $return->id = 0;
        $return->link = optional_param("link", "", PARAM_TEXT);
        $return->title = optional_param("title", "", PARAM_TEXT);
        $return->menuid = optional_param("menuid", 0, PARAM_INT);
        $return->inheader = optional_param("inheader", 1, PARAM_INT);
        $return->icon = optional_param("icon", "", PARAM_TEXT);

        return $return;
    }
}
