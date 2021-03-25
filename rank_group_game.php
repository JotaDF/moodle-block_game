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
 * Game block config form definition
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/game/libgame.php');
require_once($CFG->libdir . '/filelib.php');

require_login();

global $USER, $SESSION, $COURSE, $OUTPUT, $CFG;


$courseid = required_param('id', PARAM_INT);


$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$game = new stdClass();
$game = $SESSION->game;

require_login($course);
$PAGE->set_pagelayout('course');
$PAGE->set_url('/blocks/game/rank_game.php', array('id' => $courseid));
$PAGE->set_context(context_course::instance($courseid));
$PAGE->set_title(get_string('rank_group_game_title', 'block_game'));
$PAGE->set_heading(get_string('rank_group_game_title', 'block_game'));

echo $OUTPUT->header();
$cfggame = get_config('block_game');
if ($courseid == 1) {
    $game->config = $cfggame;
}
$limit = 0;
if ($game->config->show_rank == 1) {
    $outputhtml = '<div class="rank">';
    if ($courseid != 1) {
        $outputhtml .= '<h3>( ' . $course->fullname . ' ) </h3><br/>';

        $outputhtml .= '<table border="0" width="100%">';
        if($game->config->rank_group_calc == 1){
            $rs = ranking_group_md($courseid);
        }else {
           $rs = ranking_group($courseid); 
        }
        
        $ord = 1;
        foreach ($rs as $group) {

            $ordtxt = $ord . '&ordm;';
            
            $grouptxt = $group->name;
            $groupcount = $group->members;
            $scoretxt = $group->pt;
            if($game->config->rank_group_calc == 1){
                $scoretxt = $group->md;
            }
            $group = $DB->get_record('groups', array('id'=>$group->id), '*', MUST_EXIST);
            
            $outputhtml .= '<tr>';
            $outputhtml .= '<td width="10%" align="center">' . $ordtxt . '</td>';
            $outputhtml .= '<td width="10%" align="right">' . print_group_picture($group, $courseid, false, true, false). '</td>';
            $outputhtml .= '<td  width="70%" align="left"> ' . $grouptxt . '(' . $groupcount . ')</td>';
            $outputhtml .= '<td width="10%" align="left"> ' . $scoretxt . '</td>';
            $outputhtml .= '</tr>';
            
            $outputhtml .= '<tr><td colspan="4"><hr/></td></tr>';
            
            $ord++;
        }
        $outputhtml .= '</table>';
    }
    $outputhtml .= '</div>';
}
echo $outputhtml;

echo $OUTPUT->footer();
