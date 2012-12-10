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
 * Add page_hint administration menu settings
 * @package   local_page_hints
 * @copyright 2012 Burton and South Derbyshire College (http://moodle.com)
 * @author    Mark Ward
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/// Add page_hint administration pages to the Moodle administration menu
if ($hassiteconfig) { // needs this condition or there is error on login page
    $ADMIN->add('localplugins', new admin_externalpage('page_hints', get_string('pluginname', 'local_page_hints'),
            $CFG->wwwroot."/local/page_hints/admin/manage.php",
            'moodle/site:config'));
}