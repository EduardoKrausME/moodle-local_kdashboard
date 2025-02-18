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
 * Lib file
 *
 * introduced 23/05/17 17:59
 *
 * @package   local_kdashboard
 * @copyright 2017 Eduardo Kraus {@link http://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function local_kdashboard_extends_navigation
 *
 * @param global_navigation $nav
 *
 * @throws Exception
 */
function local_kdashboard_extends_navigation(global_navigation $nav) {
    local_kdashboard_extend_navigation($nav);
}

/**
 * Function local_kdashboard_extend_navigation
 *
 * @param global_navigation $nav
 *
 * @throws Exception
 */
function local_kdashboard_extend_navigation(global_navigation $nav) {
    global $CFG, $PAGE;

    require_once(__DIR__ . "/locallib.php");

    $CFG->custommenuitems = trim($CFG->custommenuitems);
    $CFG->custommenuitems = preg_replace('/.*kopere_dashboard.*/', "", $CFG->custommenuitems);
    $CFG->custommenuitems = trim($CFG->custommenuitems);

    if (isloggedin()) {
        if ($CFG->branch > 400 && @get_config("local_kdashboard", "menu")) {
            $context = context_system::instance();
            $hascapability = has_capability("local/kopere_dashboard:view", $context) ||
                has_capability("local/kopere_dashboard:manage", $context);

            if ($hascapability) {
                $name = get_string("modulename", "local_kdashboard");
                $link = local_kdashboard_makeurl("dashboard", "start");
                $PAGE->requires->js_call_amd("local_kdashboard/start_load",
                    "moremenu", [$name, $link]);
            }
            if (@get_config("local_kdashboard", "menuwebpages")) {
                kdashboard_add_pages_custommenuitems_400();
            }
        } else {
            $context = context_system::instance();
            if (has_capability("local/kopere_dashboard:view", $context) ||
                has_capability("local/kopere_dashboard:manage", $context)) {

                $node = $nav->add(
                    get_string("pluginname", "local_kdashboard"),
                    new moodle_url(local_kdashboard_makeurl("dashboard", "start")),
                    navigation_node::TYPE_CUSTOM,
                    null,
                    "kopere_dashboard",
                    new pix_icon("icon", get_string("pluginname", "local_kdashboard"), "local_kdashboard")
                );

                $node->showinflatnavigation = true;
            }
        }
    } else {
        if ($CFG->branch > 400 && @get_config("local_kdashboard", "menu")) {
            kdashboard_add_pages_custommenuitems_400();
        }
    }
}

/**
 * Function kdashboard_add_pages_custommenuitems_400
 */
function kdashboard_add_pages_custommenuitems_400() {
    global $CFG;

    $cache = \cache::make("local_kdashboard", "report_getdata_cache");
    if (false && $cache->has("local_kdashboard_menu")) {
        $CFG->extramenu = $cache->get("local_kdashboard_menu");
    } else {

        try {
            $CFG->extramenu = "";
            local_kdashboard_extend_navigation__get_menus(0, "");
            $cache->set("local_kdashboard_menu", $CFG->extramenu);
        } catch (dml_exception $e) { // phpcs:disable
        }
    }

    $CFG->custommenuitems = "{$CFG->custommenuitems}\n{$CFG->extramenu}";
}

/**
 * Function local_kdashboard_extend_navigation__get_menus
 *
 * @param $menuid
 * @param $prefix
 *
 * @throws dml_exception
 */
function local_kdashboard_extend_navigation__get_menus($menuid, $prefix) {
    global $DB, $CFG;

    $menus = $DB->get_records_sql("
                SELECT *
                  FROM {local_kdashboard_menu}
                 WHERE menuid   = :menuid
                   AND inheader = 1",
        ["menuid" => $menuid]);

    foreach ($menus as $menu) {
        $where = ["visible" => 1, "menuid" => $menu->id];
        $webpages = $DB->get_records("local_kdashboard_pages", $where, "pageorder ASC");
        $CFG->extramenu .= "{$prefix} {$menu->title}|{$CFG->wwwroot}/local/kopere_dashboard/?menu={$menu->link}\n";
        if ($webpages) {
            /** @var \local_kdashboard\vo\local_kdashboard_pages $webpage */
            foreach ($webpages as $webpage) {
                $link = "{$CFG->wwwroot}/local/kopere_dashboard/?p={$webpage->link}";
                $CFG->extramenu .= "{$prefix}- {$webpage->title}|{$link}\n";
            }
        }
        local_kdashboard_extend_navigation__get_menus($menu->id, "{$prefix}-");
    }
}

/**
 * Function local_kdashboard_pluginfile
 *
 * @param $course
 * @param $cm
 * @param $context
 * @param $filearea
 * @param $args
 * @param $forcedownload
 * @param array $options
 *
 * @return bool
 * @throws coding_exception
 */
function local_kdashboard_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    if ($filearea == "overviewfiles") {
        $filename = array_pop($args);
        $filepath = $args ? "/" . implode("/", $args) . "/" : "/";
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, "course", $filearea, 0, $filepath, $filename);
        if (!$file || $file->is_directory()) {
            die("ops...");
        }

        send_stored_file($file, 0, 0, $forcedownload, $options);
    }

    $fs = get_file_storage();
    if (!$file = $fs->get_file($context->id, "local_kdashboard", "editor_webpages", $args[0], "/", $args[1])) {
        return false;
    }
    send_stored_file($file, 0, 0, $forcedownload, $options);
    return true;
}
