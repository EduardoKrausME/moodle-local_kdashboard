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
 * Editor.
 *
 * @package   local_kdashboard
 * @copyright 2024 Eduardo kraus (http://eduardokraus.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../../config.php");
require_once("./function.php");
$PAGE->set_context(\context_system::instance());

$page = required_param("page", PARAM_TEXT);
$id = required_param("id", PARAM_TEXT);
$link = optional_param("link", "", PARAM_TEXT);

$html = "";

$aos = true;

if (file_exists(__DIR__ . "/_default/default-{$page}.html")) {
    if ($page == "webpages") {
        $html = $DB->get_field("local_kdashboard_pages", "text", ["id" => $id]);
    } else if ($page == "notification") {
        $html = $DB->get_field("local_kdashboard_event", "message", ["id" => $id]);
    }

    if (!isset($html[40])) {
        $html = file_get_contents(__DIR__ . "/_default/default-{$page}.html");
        $html = vvveb__changue_langs($html, "local_kdashboard");
    }
} else {
    $html = get_config("local_kdashboard", $id);
    $aos = false;
}

if (!strpos($html, "vvvebjs-styles")) {
    $html .= "\n<style id=\"vvvebjs-styles\"></style>";
}

if ($aos) {
    $html = "
<html>
    <head>
        <link vvveb-remove=\"true\" href=\"{$CFG->wwwroot}/local/kdashboard/_editor/css/bootstrap-vvveb.css\" rel=\"stylesheet\">
        <link href=\"{$CFG->wwwroot}/local/kdashboard/_editor/libs/aos/aos.css\" rel=\"stylesheet\">
        <link href=\"{$CFG->wwwroot}/local/kdashboard/_editor/libs/aos/aos.js\" rel=\"stylesheet\">
    </head>
    <body>
        {$html}
    </body>
</html>";
}
die($html);
