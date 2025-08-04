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
 * Rank Group definition
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_game\output;

use renderable;
use templatable;
use stdClass;
use renderer_base;

/**
 * Renderable class for the ranking group page.
 *
 * @package    block_game
 * @copyright  2025 José Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rankgroup implements renderable, templatable {
    /** @var title */
    public $title;
    /** @var coursefullname */
    public $coursefullname;
    /** @var isfrontpage */
    public $isfrontpage;
    /** @var notconfigured */
    public $notconfigured;
    /** @var groups */
    public $groups = [];
    /**
     * Rankgroup constructor.
     *
     * @param stdClass $course
     * @param stdClass $config
     * @param bool $isfrontpage
     */
    public function __construct(stdClass $course, stdClass $config, bool $isfrontpage) {
        $this->title = get_string('rank_group_game_title', 'block_game');
        $this->coursefullname = $course->fullname;
        $this->isfrontpage = $isfrontpage;
        $this->notconfigured = !(isset($config->show_rank) && $config->show_rank == 1);

        if ($this->notconfigured || $isfrontpage) {
            return;
        }

        global $DB;

        $rankingdata = (isset($config->rank_group_calc) && $config->rank_group_calc == 1)
            ? block_game_ranking_group_md($course->id)
            : block_game_ranking_group($course->id);

        $ord = 1;
        foreach ($rankingdata as $groupdata) {
            $grouprecord = $DB->get_record('groups', ['id' => $groupdata->id], '*', MUST_EXIST);

            $score = isset($config->rank_group_calc) && $config->rank_group_calc == 1
                ? number_format($groupdata->md, 2)
                : number_format($groupdata->pt, 2);

            $this->groups[] = [
                'ord' => $ord,
                'groupname' => $groupdata->name,
                'members' => $groupdata->members,
                'score' => $score,
                'picture' => print_group_picture($grouprecord, $course->id, false, true, true),
            ];

            $ord++;
        }
    }
    /**
     * Export the data.
     * @param renderer_base $output
     * @return array|\stdClass
     */
    public function export_for_template(renderer_base $output) {
        return [
            'title' => $this->title,
            'coursefullname' => $this->coursefullname,
            'isfrontpage' => $this->isfrontpage,
            'notconfigured' => $this->notconfigured,
            'groups' => $this->groups,
        ];
    }
}
