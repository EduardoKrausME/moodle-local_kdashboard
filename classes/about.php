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
 * about file
 *
 * introduced 01/06/17 15:44
 *
 * @package   local_kdashboard
 * @copyright 2017 Eduardo Kraus {@link http://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kdashboard;

use local_kdashboard\util\dashboard_util;

/**
 * Class about
 *
 * @package local_kdashboard
 */
class about {

    /**
     * Function dashboard
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function dashboard() {
        dashboard_util::add_breadcrumb(get_string_kopere("about_title"));
        dashboard_util::start_page();

        echo '<div class="element-box">
                  <p><img src="https://www.eduardokraus.com/logos/kdashboard.svg" style="max-width: 100%" /></p>
                  <p>&nbsp;</p>
                  <p>' . get_string_kopere("about_project") . '
                     <a target="_blank" href="https://www.eduardokraus.com/kopere-dashboard">Eduardo Kraus</a>.</p>
                  <p>' . get_string_kopere("about_code") . '
                     <a target="_blank" href="https://github.com/EduardoKrausME/moodle-local-kdashboard"
                     >github.com/EduardoKrausME/moodle-local-kdashboard</a>.</p>
                  <p>' . get_string_kopere("about_help") . '
                     <a target="_blank" href="https://github.com/EduardoKrausME/moodle-local-kdashboard/wiki"
                     >Wiki</a>.</p>
                  <p>' . get_string_kopere("about_bug") . '
                     <a href="https://github.com/EduardoKrausME/moodle-local-kdashboard/issues"
                        target="_blank">issue</a>.</p>
              </div>';

        dashboard_util::end_page();
    }
}
