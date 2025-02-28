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
 * Settings file
 *
 * introduced 23/05/17 17:59
 *
 * @package   local_kdashboard
 * @copyright 2017 Eduardo Kraus {@link http://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings = new admin_settingpage("kdashboard", get_string("pluginname", "local_kdashboard"));
$ADMIN->add("localplugins", $settings);

if ($hassiteconfig) {
    if (!$ADMIN->locate("integracaoroot")) {
        $ADMIN->add("root", new admin_category("integracaoroot", get_string("integracaoroot", "local_kdashboard")));
    }

    $ADMIN->add("integracaoroot",
        new admin_externalpage(
            "local_kdashboard",
            get_string("modulename", "local_kdashboard"),
            "{$CFG->wwwroot}/local/kdashboard/view.php?classname=dashboard&method=start"
        )
    );
}

if ($ADMIN->fulltree) {

    if (method_exists($settings, "add")) {

        $setting = new admin_setting_configcheckbox("local_kdashboard/menu",
            get_string("kdashboard_menu", "local_kdashboard"),
            get_string("kdashboard_menu_desc", "local_kdashboard"), 1
        );
        $setting->set_updatedcallback("theme_reset_all_caches");
        $settings->add($setting);

        $setting = new admin_setting_configcheckbox("local_kdashboard/menuwebpages",
            get_string("kdashboard_menuwebpages", "local_kdashboard"),
            get_string("kdashboard_menuwebpages_desc", "local_kdashboard"), 1
        );
        $setting->set_updatedcallback("theme_reset_all_caches");
        $settings->add($setting);

        $settings->add(
            new admin_setting_configcheckbox("local_kdashboard/monitor",
                get_string("kdashboard_monitor", "local_kdashboard"),
                get_string("kdashboard_monitor_desc", "local_kdashboard"),
                0
            ));

        $icon = $OUTPUT->image_url("google-fonts", "local_kdashboard")->out(false);
        $settings->add(
            new admin_setting_configtextarea("kdashboard_pagefonts",
                get_string("kdashboard_pagefonts", "local_kdashboard"),
                get_string("kdashboard_pagefonts_desc", "local_kdashboard", $icon), ""
            ));

        $plugins = glob(__DIR__ . "/../*/settings_kopere.php");
        foreach ($plugins as $plugin) {
            require($plugin);
        }
    }
}
