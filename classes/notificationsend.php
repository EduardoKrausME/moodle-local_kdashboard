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
 * Notification Send file
 *
 * @package   local_kdashboard
 * @copyright 2025 Eduardo Kraus {@link http://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_kdashboard;

use local_kdashboard\html\button;
use local_kdashboard\html\table;
use local_kdashboard\html\form;
use local_kdashboard\html\inputs\input_htmleditor;
use local_kdashboard\html\inputs\input_select;
use local_kdashboard\html\inputs\input_text;
use local_kdashboard\html\table_header_item;
use local_kdashboard\output\events\send_events;
use local_kdashboard\util\config;
use local_kdashboard\util\dashboard_util;
use local_kdashboard\util\message;
use local_kdashboard\util\release;

/**
 * Class notificationsend
 *
 * @package local_kdashboard
 */
class notificationsend {
    /**
     * Function create
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function create() {
        global $DB, $CFG, $PAGE, $USER;

        dashboard_util::add_breadcrumb(get_string_kopere("notificationsend_title"));
        dashboard_util::start_page();

        notificationsutil::message_no_smtp();

        echo '<div class="element-box">';

        $courseid = optional_param("course", false, PARAM_INT);

        $form = new form(local_kdashboard_makeurl("notificationsend", "create"));

        $values = array_merge(
            [(object)["id" => 0, "fullname" => ""]],
            $DB->get_records_select("course", "id >= 2", [], "fullname ASC", "id, fullname")
        );
        $form->add_input(
            input_select::new_instance()
                ->set_title(get_string_kopere("notificationsend_course"))
                ->set_name("course")
                ->set_value($courseid)
                ->set_values($values, "id", "fullname")
        );
        $form->close_and_auto_submit_input("course");

        if ($courseid) {
            $form = new form(local_kdashboard_makeurl("notificationsend", "create_send"));
            $form->create_hidden_input("course", $courseid);

            $form->panel_start(get_string_kopere("notificationsend_criteria"), "notificationsend_criteria");
            $form->add_input(
                input_text::new_instance()
                    ->set_type("number")
                    ->set_value(0)
                    ->set_name("criteria_days")
                    ->set_title(get_string_kopere("notificationsend_criteria_days"))
                    ->set_description(get_string_kopere("notificationsend_criteria_days_desc"))
            );
            $form->add_input(
                input_text::new_instance()
                    ->set_type("number")
                    ->set_value(0)
                    ->set_name("criteria_enrol")
                    ->set_title(get_string_kopere("notificationsend_criteria_enrol"))
                    ->set_description(get_string_kopere("notificationsend_criteria_enrol_desc"))
            );

            $fields = $DB->get_records_sql(
                "SELECT * FROM {user_info_field} WHERE datatype IN ('text','menu','checkbox') ORDER BY name ASC");
            foreach ($fields as $field) {
                if ($field->datatype == "text" || $field->datatype == "textarea") {

                    $sql = "SELECT DISTINCT data FROM {user_info_data} WHERE fieldid = :fieldid";
                    $datas = $DB->get_records_sql($sql, ["fieldid" => $field->id], 0, 35);
                    $datas = array_merge(
                        [(object)["data" => ""]],
                        $datas
                    );
                    if (count($datas) < 34) {
                        $form->add_input(
                            input_select::new_instance()
                                ->set_value("")
                                ->set_values($datas, "data", "data")
                                ->set_name("user_info_field[{$field->shortname}]")
                                ->set_title($field->name)
                                ->set_description($field->description)
                        );
                    } else {
                        $form->add_input(
                            input_text::new_instance()
                                ->set_name("user_info_field[{$field->shortname}]")
                                ->set_title($field->name)
                                ->set_description($field->description)
                        );
                    }

                } else if ($field->datatype == "menu") {
                    $params = explode("\n", $field->param1);
                    $values = [["key" => "", "value" => ""]];
                    foreach ($params as $param) {
                        $values  [] = ["key" => $param, "value" => $param];
                    }

                    $form->add_input(
                        input_select::new_instance()
                            ->set_value("")
                            ->set_values($values)
                            ->set_name("user_info_field[{$field->shortname}]")
                            ->set_title($field->name)
                            ->set_description($field->description)
                    );
                } else if ($field->datatype == "checkbox") {
                    $form->add_input(
                        input_select::new_instance()
                            ->set_value("")
                            ->set_values([
                                ["key" => "", "value" => ""],
                                ["key" => "1", "value" => "Sim"],
                                ["key" => "2", "value" => "Não"],
                            ])
                            ->set_name("user_info_field[{$field->shortname}]")
                            ->set_title($field->name)
                            ->set_description($field->description)
                    );
                }
            }

            echo "<div id='notificationsend-link'></div>";
            $form->panel_close();
            $PAGE->requires->strings_for_js([
                "notificationsend_viewstudentswithcriteria",
            ], "local_kdashboard");
            $PAGE->requires->js_call_amd("local_kdashboard/notificationsend", "list_users");

            $form->add_input(
                input_text::new_instance()
                    ->set_title(get_string_kopere("notification_from"))
                    ->set_name("notification_from")
                    ->set_value(fullname($USER))
                    ->set_description(get_string_kopere("notification_fromdesc"))
                    ->set_required());

            $form->add_input(
                input_text::new_instance()
                    ->set_title(get_string_kopere("notification_subject"))
                    ->set_name("subject")
                    ->set_description(get_string_kopere("notification_subjectdesc"))
                    ->set_required());

            $form->print_row(null,
                button::help("TAGS-substituídas-nas-mensagens-de-Notificações", get_string_kopere("notification_tags")));

            $templatecontent = config::get_key("notification-template");

            $href = "{$CFG->wwwroot}/local/kopere_dashboard/_editor/?page=settings&id=notification-template";
            $edittemplate = "<a href='{$href}' class='btn btn-info mt-2'>" .
                get_string_kopere("notification_message_edit_template") .
                "</a>";
            if (strpos($templatecontent, "{[message]}") === false) {
                message::print_danger(get_string_kopere("notification_message_template_error") . "<br>" . $edittemplate);
            } else {
                $htmltexteditor = input_htmleditor::new_instance()
                    ->set_name("notification_message")
                    ->set_value(get_string_kopere("notification_message_html"))
                    ->set_style("height:400px;")
                    ->to_string();

                $templatecontent = str_replace("{[message]}", $htmltexteditor, $templatecontent);
                $form->print_panel(get_string_kopere("notification_message"), $templatecontent . $edittemplate);

                $form->create_submit_input(get_string_kopere("notificationsend_send"));
            }
            $form->close();
        }

        echo "</div>";
        dashboard_util::end_page();
    }

    /**
     * Function get_sql
     *
     * @param $fields
     *
     * @return array
     * @throws \coding_exception
     */
    private function get_sql($fields) {
        $fieldsparams = optional_param_array("user_info_field", "", PARAM_TEXT);
        $courseid = required_param("course_id", PARAM_INT);
        $criteriadays = required_param("criteria_days", PARAM_INT);
        $criteriaenrol = required_param("criteria_enrol", PARAM_INT);

        $params = [
            "courseid" => $courseid,
            "criteria_days" => time() - ($criteriadays * 24 * 60 * 60),
            "criteria_enrol" => time() - ($criteriaenrol * 24 * 60 * 60),
        ];

        $wheres = [];
        foreach ($fields as $field) {

            if ($field->datatype == "text" || $field->datatype == "textarea") {
                if (isset($fieldsparams[$field->shortname])) {
                    $wheres[] = "u.id IN( SELECT userid FROM {user_info_data} WHERE fieldid = :fieldid_{$field->id}_id AND data LIKE :fieldid_{$field->id}_value )";

                    $params["fieldid_{$field->id}_id"] = $field->id;
                    $params["fieldid_{$field->id}_value"] = "%{$fieldsparams[$field->shortname]}%";
                }

            } else if ($field->datatype == "menu") {
                if (isset($fieldsparams[$field->shortname])) {
                    $wheres[] = "u.id IN( SELECT userid FROM {user_info_data} WHERE fieldid = :fieldid_{$field->id}_id AND data = :fieldid_{$field->id}_value )";

                    $params["fieldid_{$field->id}_id"] = $field->id;
                    $params["fieldid_{$field->id}_value"] = $fieldsparams[$field->shortname];
                }
            } else if ($field->datatype == "checkbox") {
                if (isset($fieldsparams[$field->shortname])) {
                    $wheres[] = "u.id IN( SELECT userid FROM {user_info_data} WHERE fieldid = :fieldid_{$field->id}_id AND data = :fieldid_{$field->id}_value )";

                    $params["fieldid_{$field->id}_id"] = $field->id;
                    $params["fieldid_{$field->id}_value"] = $fieldsparams[$field->shortname];
                }
            }
        }

        $wheresql = "";
        if (isset($wheres[0])) {
            $wheresql = implode("\n\t\tOR\n\t\t\t", $wheres);
            $wheresql = "AND (\n\t\t\t{$wheresql} \n\t\t)";
        }

        $sql = "
            SELECT DISTINCT u.id,
                            CONCAT(u.firstname, ' ', u.lastname) AS fullname,
                            u.username,
                            u.email,
                            u.phone1,
                            ue.status,
                            ula.timeaccess AS lastacess,
                            ue.timecreated AS enrolcreated
            FROM {user_enrolments}       ue
            JOIN {user}                   u ON u.id = ue.userid
            JOIN {enrol}                  e ON e.id = ue.enrolid
            JOIN {course}                 c ON c.id = e.courseid
            LEFT JOIN {user_lastaccess} ula ON ula.userid = ue.userid
                                           AND ula.courseid = c.id
            WHERE c.id = :courseid
              {$wheresql}";


        if ($criteriadays) {
            $sql .= "\nAND ue.timecreated < :criteria_days";
        }
        if ($criteriaenrol) {
            $sql .= "\nAND (ula.timeaccess IS NULL OR ula.timeaccess < :criteria_enrol)";
        }

        return [$sql, $params];
    }

    /**
     * Function list_users
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function list_users() {
        global $DB;

        dashboard_util::add_breadcrumb(get_string_kopere("notificationsend_test_title"));
        dashboard_util::start_page();

        echo '<div class="element-box">';

        $fields = $DB->get_records_sql(
            "SELECT * FROM {user_info_field} WHERE datatype IN ('text','menu','checkbox') ORDER BY name ASC");

        list($sql, $params) = $this->get_sql($fields);
        $rows = $DB->get_records_sql($sql, $params, 0, 100);
        foreach ($rows as $id => $row) {
            foreach ($fields as $field) {
                $rows[$id]->{$field->shortname} =
                    $DB->get_field("user_info_data", "data", ["userid" => $row->id, "fieldid" => $field->id]);
            }
        }

        message::print_info("Visualização limitado a 100 registros!");

        $table = new table();
        $table->add_header("#", "id", null, table_header_item::TYPE_INT, "width: 20px");
        $table->add_header(get_string_kopere("user_table_fullname"), "fullname");
        $table->add_header(get_string_kopere("user_table_username"), "username");
        $table->add_header(get_string_kopere("user_table_email"), "email");
        $table->add_header(get_string_kopere("profile_enrol_start"), "enrolcreated",
            "\\local_kdashboard\\notificationsend::time_to_date");
        $table->add_header(get_string_kopere("profile_access_last"), "lastacess",
            "\\local_kdashboard\\notificationsend::time_to_date");

        foreach ($fields as $field) {
            $table->add_header($field->name, $field->shortname);
        }

        $table->set_row($rows);
        $table->close();

        echo "</div>";
        dashboard_util::end_page();
    }

    /**
     * Function create_send
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function create_send() {
        global $DB, $CFG, $USER;

        ini_set("max_execution_time", 0);
        session_write_close();

        $courseid = required_param("course_id", PARAM_INT);
        $criteriadays = required_param("criteria_days", PARAM_INT);
        $criteriaenrol = required_param("criteria_enrol", PARAM_INT);

        $fields = $DB->get_records_sql(
            "SELECT * FROM {user_info_field} WHERE datatype IN ('text','menu','checkbox') ORDER BY name ASC");

        list($sql, $params) = $this->get_sql($fields);
        $rows = $DB->get_records_sql($sql, $params, 0, 100);

        $userfrom = $USER;
        $userfrom->fullname = required_param("notification_from", PARAM_TEXT);

        foreach ($rows as $row) {
            $userto = $DB->get_record("user", ["id" => $row->id]);
            $userto->fullname = fullname($userto);

            $userto->email = $USER->email;

            $subject = required_param("notification_subject", PARAM_TEXT);
            $message = required_param("notification_message", PARAM_TEXT);

            $sendsubject = send_events::replace_tag_user($subject, $userto, "to");
            $htmlmessage = send_events::replace_tag_user($message, $userto, "to");

            $magager = "<a href='{$CFG->wwwroot}/message/notificationpreferences.php'>" .
                get_string_kopere("notification_manager") . "</a>";
            $htmlmessage = str_replace("{[manager]}", $magager, $htmlmessage);

            $eventdata = new \core\message\message();
            if (release::version() >= 3.2) {
                $eventdata->courseid = $courseid;
                $eventdata->modulename = "moodle";
            }
            $eventdata->component = "local_kdashboard";
            $eventdata->name = "kopere_dashboard_messages";
            $eventdata->userfrom = $userfrom;
            $eventdata->userto = $userto;
            $eventdata->subject = $sendsubject;
            $eventdata->fullmessage = html_to_text($htmlmessage);
            $eventdata->fullmessageformat = FORMAT_HTML;
            $eventdata->fullmessagehtml = $htmlmessage;
            $eventdata->smallmessage = "";

            message_send($eventdata);
        }
    }

    /**
     * Function time_to_date
     *
     * @param $data
     * @param $key
     *
     * @return string
     */
    public static function time_to_date($data, $key) {
        return userdate($data->$key);
    }
}