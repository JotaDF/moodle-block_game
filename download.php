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
 * Game block caps.
 *
 * @package    block_game
 * @copyright  José Wilson <j.wilson.df@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/game/lib.php');
require_once($CFG->libdir . "/dataformatlib.php");

require_login();

$id = required_param('id', PARAM_INT);
$op = optional_param('op', '', PARAM_ALPHA);
$dataformat = optional_param('dataformat', '', PARAM_ALPHA);

if ($op === "ranking") {
    $columns = [get_string('order', 'block_game'),
                get_string('name', 'block_game'),
                get_string('score_atv', 'block_game'),
                get_string('score_mod', 'block_game'),
                get_string('score_section', 'block_game'),
                get_string('score_bonus_day', 'block_game'),
                get_string('score_total', 'block_game')];
    $rs = block_game_rank_list($id);
    $ord = 1;
    $rows = array();
    foreach ($rs as $gamer) {
        $ordtxt = $ord;
        $usertxt = $gamer->firstname . ' ' . $gamer->lastname;
        $scoreatv = $gamer->sum_score_activities;
        $scoremod = $gamer->sum_score_module_completed;
        $scoresection = $gamer->sum_score_section;
        $scorebonusday = $gamer->sum_score_bonus_day;
        $scoretxt = $gamer->pt;
        $rows[] = [$ordtxt, $usertxt, $scoreatv, $scoremod,
                   $scoresection, $scorebonusday, $scoretxt];

        $ord++;
    }

    download_as_dataformat('ranking', $dataformat, $columns, $rows);
}
