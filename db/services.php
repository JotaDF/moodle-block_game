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
 * Block Game external functions and service definitions.
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'block_game_get_count_course' => array(
        'classname' => 'block_game_external',
        'methodname' => 'get_count_course',
        'classpath' => 'block/block_game/externallib.php',
        'description' => 'Returns count courses',
        'type' => 'read',
        'capabilities' => ''
    )
);

$services = array(
   'Block Game plugin webservices'  => array(
        'functions' => array ('block_game_get_count_course'), // Unused as we add the service in each function definition, third party services would use this.
        'enabled' => 0,
        'restrictedusers' => 0,
        'shortname' => 'block_game_ws',
        'downloadfiles' => 1,
        'uploadfiles' => 1
    ),
);

// We defined the web service functions to install.
$functions = array(
        'block_game_get_count_course' => array(
                'classname'   => 'local_wstemplate_external',
                'methodname'  => 'hello_world',
                'classpath'   => 'local/wstemplate/externallib.php',
                'description' => 'Return Hello World FIRSTNAME. Can change the text (Hello World) sending a new text as parameter',
                'type'        => 'read',
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'My service' => array(
                'functions' => array ('local_wstemplate_hello_world'),
                'restrictedusers' => 0,
                'enabled'=>1,
        )
);