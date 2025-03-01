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
 * phpcs:disable moodle.Files.RequireLogin.Missing
 *
 * index file
 *
 * introduced 23/05/17 17:59
 *
 * @package   local_kdashboard
 * @copyright 2017 Eduardo Kraus {@link http:// eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_kdashboard\util\config;

require_once("../../config.php");
require_once("autoload.php");
global $DB, $PAGE, $OUTPUT;

$menulink = optional_param("menu", false, PARAM_TEXT);
$pagelink = optional_param("p", false, PARAM_TEXT);

$htmldata = optional_param("htmldata", false, PARAM_RAW);
if ($htmldata && confirm_sesskey()) {
    $pagelink = optional_param("link", false, PARAM_TEXT);
}

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->add_body_class("kopere-dashboard-pages");
$PAGE->set_pagetype("my-index");

if ($pagelink) {
    $sql = "SELECT * FROM {local_kdashboard_pages} WHERE link LIKE :link";

    /** @var \local_kdashboard\vo\local_kdashboard_pages $webpages */
    $webpages = $DB->get_record_sql($sql, ["link" => $pagelink]);

    if ($webpages == null) {
        $PAGE->set_url(new moodle_url("/local/kdashboard/"));
        \local_kdashboard\util\webpages_util::notfound("webpages_error_page");
    }

    if ($htmldata && confirm_sesskey()) {
        $webpages->text = $htmldata;
    }

    $PAGE->set_url(new moodle_url("/local/kdashboard/?p={$pagelink}"));
    $PAGE->set_pagelayout($webpages->theme);
    $PAGE->set_title($webpages->title);

    $edit = "";
    $hascapability = has_capability("local/kdashboard:manage", $context);
    if ($hascapability) {
        $href = local_kdashboard_makeurl("webpages", "page_edit", ["id" => $webpages->id]);
        $edittext = get_string_kopere("webpages_page_edit");
        $edit = " - <a href='{$href}' target=\"_blank\" style='text-decoration:underline'>{$edittext}</a>";
    }
    $PAGE->set_heading("{$webpages->title} {$edit}", false);

    /** @var \local_kdashboard\vo\local_kdashboard_menu $menu */
    $menu = $DB->get_record("local_kdashboard_menu", ["id" => $webpages->menuid]);

    $PAGE->navbar->add(get_string_kopere("webpages_allpages"), new moodle_url("/local/kdashboard/"));
    $PAGE->navbar->add($menu->title, new moodle_url("/local/kdashboard/?menu={$menu->link}"));
    $PAGE->navbar->add($webpages->title);

    echo \local_kdashboard\fonts\font_util::print_only_unique();
    echo $OUTPUT->header();

    preg_match_all('/\[\[(kopere_\w+)::(\w+)(->|-&gt;)(\w+)\((.*?)\)]]/', $webpages->text, $classes);

    foreach ($classes[0] as $key => $replace) {
        $classname = $classes[1][$key];
        $function = $classes[2][$key];
        $metodo = $classes[4][$key];
        $parametro = $classes[5][$key];
        $class = "\\local_{$classname}\\{$function}";

        if (class_exists($class)) {
            if (method_exists($class, $metodo)) {
                $newreplace = $class::$metodo($parametro);

                $webpages->text = str_replace($replace, $newreplace, $webpages->text);
            }
        }
    }
    echo '<div class="container">';

    preg_match_all('/\[\[(kopere_\w+)::(\w+)(->|-&gt;)(\w+)\((.*?)\)]]/', $webpages->text, $classes);
    foreach ($classes[0] as $key => $replace) {
        $classname = $classes[1][$key];
        $function = $classes[2][$key];
        $metodo = $classes[4][$key];
        $parametro = $classes[5][$key];
        $class = "\\local_{$classname}\\{$function}";

        if (class_exists($class)) {
            if (method_exists($class, $metodo)) {
                $newreplace = $class::$metodo($parametro);

                $webpages->text = str_replace($replace, $newreplace, $webpages->text);
            }
        }
    }

    echo $webpages->text;
    $PAGE->requires->js_call_amd("local_kdashboard/webpages", "view_page");

    echo "</div>";

    \local_kdashboard\util\webpages_util::analytics();
    echo $OUTPUT->footer();
} else {
    if ($menulink) {
        /** @var \local_kdashboard\vo\local_kdashboard_menu $menu */
        $menu = $DB->get_record("local_kdashboard_menu", ["link" => $menulink]);
        if ($menu == null) {
            \local_kdashboard\util\webpages_util::notfound("webpages_error_menu");
        }

        $PAGE->set_url(new moodle_url("/local/kdashboard/?menu={$menu->link}"));
        $PAGE->set_pagelayout(get_config("local_kdashboard", "webpages_theme"));
        $PAGE->set_title($menu->title);
        $PAGE->set_heading($menu->title);

        $PAGE->navbar->add(get_string_kopere("webpages_allpages"), new moodle_url("/local/kdashboard/"));
        $PAGE->navbar->add($menu->title);

        $menus = [$menu];
    } else {
        $PAGE->set_url(new moodle_url("/local/kdashboard/"));
        $PAGE->set_pagelayout(get_config("local_kdashboard", "webpages_theme"));
        $PAGE->set_title(get_string_kopere("webpages_allpages"));
        $PAGE->set_heading(get_string_kopere("webpages_allpages"));

        $PAGE->navbar->add(get_string_kopere("webpages_allpages"), new moodle_url("/local/kdashboard/"));

        $menus = $DB->get_records_sql("SELECT * FROM {local_kdashboard_menu} WHERE inheader = 1");
    }
    echo $OUTPUT->header();

    $data = ["menus" => []];

    /** @var \local_kdashboard\vo\local_kdashboard_menu $menu */
    foreach ($menus as $menu) {
        if (!$menulink) {
            $menu->menulink = [
                "link" => $menu->link,
                "title" => $menu->title,
            ];
        }

        $sql = "SELECT * FROM {local_kdashboard_pages} WHERE visible = 1 AND menuid = {$menu->id} ORDER BY pageorder ASC";
        $webpagess = $DB->get_records_sql($sql);

        /** @var \local_kdashboard\vo\local_kdashboard_pages $webpages */
        foreach ($webpagess as $webpages) {

            $webpages->link = "{$CFG->wwwroot}/local/kdashboard/?p={$webpages->link}";
            $webpages->access = get_string_kopere("webpages_access");

            if (file_exists(__DIR__ . "/../kopere_pay/lib.php") && $webpages->courseid) {
                $koperepaydetalhe = $DB->get_record("kopere_pay_detalhe", ["course" => $webpages->courseid]);
                $precoint = str_replace(".", "", $koperepaydetalhe->preco);
                $precoint = str_replace(",", ".", $precoint);
                $precoint = floatval("0{$precoint}");

                if (!$precoint) {
                    $webpages->cursopreco = get_string_kopere("webpages_free");
                } else {
                    $webpages->cursopreco = "R$ {$koperepaydetalhe->preco}";
                }

                $offprice = config::get_key("builder_offprice_{$koperepaydetalhe->course}");
                if ($offprice) {
                    $data["offprice"] = get_string_koperepay("pedido_resumo_moeda", preg_replace('/[^0-9]/', "", $offprice));
                }
            }

            $webpages->imagem = $OUTPUT->image_url("course-default", "local_kdashboard")->out(false);
            $webpages->text = \local_kdashboard\util\html::truncate_text(strip_tags($webpages->text), 300);

            if (!isset($menu->webpages)) {
                $menu->webpages = [];
            }
            $menu->webpages[] = $webpages;
        }
        $data["menus"][] = $menu;
    }

    echo $OUTPUT->render_from_template("local_kdashboard/index_webpages", $data);

    \local_kdashboard\util\webpages_util::analytics();
    echo $OUTPUT->footer();
}
