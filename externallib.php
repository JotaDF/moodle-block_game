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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * External forum API
 * 
 * @package mod_simplecertificate
 * @copyright 2014 © Carlos Alexandre S. da Fonseca
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once ("$CFG->libdir/externallib.php");

class block_game_external extends external_api {

    /**
     * To validade input parameters
     * @return external_function_parameters
     */
//    public static function get_game_user_parameters() {
//        return new external_function_parameters(
//              array(
//                  'userid' => new external_value(PARAM_TEXT, 'User ID', VALUE_REQUIRED)
//               )
//        );
//    }
    
    
    public static function get_count_course() {
        global $DB;
        
        $sql = "SELECT count(*) FROM {course} WHERE format <> 'site'";
        $busca = $DB->get_record_sql($sql, array());
         
        return $busca->total;
    }
    
    public static function get_count_user() {
        global $DB;
        
        $sql = "SELECT count(*) FROM {user} WHERE format <> 'site'";
        $busca = $DB->get_record_sql($sql, array());
         
        return $busca->total;
    }
    /**
     * Validate the return value
     * @return external_value
     */
    public static function get_count_course_returns() {
        return new external_value(PARAM_INT, 'count courses');
    }

}