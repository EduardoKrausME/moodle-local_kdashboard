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
 * Dashboard file
 *
 * introduced 30/01/17 09:39
 *
 * @package   local_kdashboard
 * @copyright 2017 Eduardo Kraus {@link http://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kdashboard;

use local_kdashboard\report\files;
use local_kdashboard\server\performancemonitor;
use local_kdashboard\util\bytes_util;
use local_kdashboard\util\dashboard_util;
use local_kdashboard\util\url_util;

/**
 * Class dashboard
 *
 * @package local_kdashboard
 */
class dashboard {

    /**
     * Function start
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function start() {
        global $PAGE;

        dashboard_util::add_breadcrumb("Kopere Dashboard");
        dashboard_util::start_page();

        echo performancemonitor::load_monitor();

        echo '
            <div id="dashboard-moodleinfo"></div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="element-box">
                        <h3>' . get_string_kopere("dashboard_grade_title") . '</h3>
                        <div id="dashboard-last_grades"></div>
                    </div>
                </div>
                <div class="col-sm-6 ">
                    <div class="element-box">
                        <h3>' . get_string_kopere("dashboard_enrol_title") . '</h3>
                        <div id="dashboard-last_enroll"></div>
                    </div>
                </div>
            </div>';

        $PAGE->requires->js_call_amd('local_kdashboard/dashboard', "start");
        dashboard_util::end_page();
    }

    /**
     * Function monitor
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function monitor() {
        echo '
            <div class="element-content">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="element-box color_user">
                            <div class="label">' . get_string_kopere("dashboard_title_user") . '</div>
                            <div class="value"><a href="' . url_util::makeurl("users", "dashboard") . '">
                                ' . users::count_all(true) . ' / ' . users::count_all_learners(true) . '</a></div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="element-box color_online">
                            <div class="label">' . get_string_kopere("dashboard_title_online") . '</div>
                            <div class="value"><a href="' . url_util::makeurl("useronline", "dashboard") . '">
                                <span id="user-count-online">' . useronline::count(5) . '</span>
                                / ' . useronline::count(60) . '</a></div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="element-box color_course">
                            <div class="label">' . get_string_kopere("dashboard_title_course") . '</div>
                            <div class="value"><a href="' . url_util::makeurl("courses", "dashboard") . '">
                            ' . courses::count_all(true) . '
                                / ' . courses::count_all_visibles(true) . '</a></div>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="element-box color_disk">
                            <div class="label">' . get_string_kopere("dashboard_title_disk") . '</div>
                            <div class="value"><a href=' .
            url_util::makeurl("reports", "dashboard", ["type" => "server"]) . '">
                            ' . bytes_util::size_to_byte(files::count_all_space()) . '</a></div>
                        </div>
                    </div>
                </div>
            </div>';
    }

    /**
     * Function last_grades
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function last_grades() {
        global $DB, $PAGE, $OUTPUT;

        $grade = new grade();
        $lastgrades = $grade->get_last_grades();

        foreach ($lastgrades as $grade) {

            $user = $DB->get_record("user", ["id" => $grade->userid]);

            $userpicture = new \user_picture($user);
            $userpicture->size = 1;
            $profileimageurl = $userpicture->get_url($PAGE)->out(false);

            if ($grade->itemtype == "mod") {
                $evaluation = get_string_kopere("dashboard_grade_inmod", $grade);
            } else if ($grade->itemtype == "course") {
                $evaluation = get_string_kopere("dashboard_grade_incourse", $grade);
            } else {
                continue;
            }

            $gradetext = number_format($grade->finalgrade, 1, get_string("decsep", "langconfig"), "") . " " .
                get_string_kopere("dashboard_grade_of") . " " . intval($grade->rawgrademax);

            $data = [
                "user_fullname" => fullname($user),
                "profileimageurl" => $profileimageurl,
                "users_details" => url_util::makeurl("users", "details", ["userid" => $user->id]),
                "users_details_ajax" => url_util::makeurl("users", "details", ["userid" => $user->id], "view-ajax"),
                "dashboard_grade_text" =>
                    get_string_kopere("dashboard_grade_text",
                        ["grade" => $gradetext, "evaluation" => $evaluation]),
                "grade_date" => userdate($grade->timemodified, get_string_kopere("dateformat")),
            ];
            echo $OUTPUT->render_from_template("local_kdashboard/last_grades", $data);
        }
        die();
    }

    /**
     * Function last_enroll
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function last_enroll() {
        global $DB, $PAGE, $OUTPUT;

        $enrol = new enroll();
        $lastenroll = $enrol->last_enroll();

        foreach ($lastenroll as $enrol) {

            $user = $DB->get_record("user", ["id" => $enrol->userid]);
            if ($user) {

                $userpicture = new \user_picture($user);
                $userpicture->size = 1;
                $profileimageurl = $userpicture->get_url($PAGE)->out(false);

                $statusmatricula = '<span class="btn-dangerpadding-0-8 border-radius-5 text-nowrap">' .
                    get_string_kopere("dashboard_enrol_inactive") . "</span>";
                if ($enrol->status == 0) {
                    $statusmatricula = '<span class="btn-successpadding-0-8 border-radius-5 text-nowrap">' .
                        get_string_kopere("dashboard_enrol_active") . "</span>";
                }

                $data = [
                    "user_fullname" => fullname($user),
                    "profileimageurl" => $profileimageurl,
                    "users_details" => url_util::makeurl("users", "details", ["userid" => $user->id]),
                    "dashboard_enrol_text" => get_string_kopere("dashboard_enrol_text", $enrol),
                    "matricula_status" => $statusmatricula,
                    "matricula_date" => userdate($enrol->timemodified, get_string_kopere("dateformat")),
                ];
                echo $OUTPUT->render_from_template("local_kdashboard/last_enroll", $data);
            }
        }
    }
}
