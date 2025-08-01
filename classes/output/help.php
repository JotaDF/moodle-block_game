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
 * Game block
 *
 * @package    help_game
 * @copyright  2025 José Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_game\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Help page renderable class.
 *
 * @package    help_game
 * @copyright  2025 José Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class help implements renderable, templatable {
    /** @var title */
    public $title;
    /** @var coursefullname */
    public $coursefullname;
    /** @var isfrontpage */
    public $isfrontpage;
    /** @var notconfigured */
    public $notconfigured;
    /** @var sections */
    public $sections = [];
    /** @var features */
    public $features = [];
    /**
     * help constructor.
     *
     * @param stdClass $course
     * @param stdClass $config
     * @param stdClass $cfggame
     * @param bool $isfrontpag
     * @param array $sections
     * $notconfigured
     */
    public function __construct(\stdClass $course, \stdClass $config, \stdClass $cfggame,
            bool $isfrontpage, bool $notconfigured, array $sections) {
        $this->coursefullname = $course->fullname;
        $this->isfrontpage = $isfrontpage;
        $this->notconfigured = $notconfigured;
        $this->sections = $sections;
        $this->title = get_string('help_game_title', 'block_game');

        // Prepara os "features" que serão exibidos condicionalmente.
        if ($cfggame->use_avatar == 1) {
            $this->features[] = [
                'key' => 'avatar',
                'image' => 'a0.svg',
                'title' => get_string('help_avatar_titulo', 'block_game'),
                'text' => ($cfggame->change_avatar_course == 1 && !$isfrontpage)
                    ? get_string('help_avatar_text_course', 'block_game')
                    : get_string('help_avatar_text', 'block_game'),
            ];
        }

        if (!empty($config->show_info)) {
            $this->features[] = [
                'key' => 'info',
                'image' => 'info.svg',
                'title' => get_string('help_info_user_titulo', 'block_game'),
                'text' => get_string('help_info_user_text', 'block_game'),
            ];
        }

        if (!empty($config->show_score)) {
            $scoretext = get_string('help_score_text', 'block_game');
            if (!empty($config->score_activities)) {
                $scoretext .= '<br>' . get_string('help_score_activities_text', 'block_game');
            }
            $scoretext .= '<br>' . get_string('help_score_modules_text', 'block_game');

            $sectionscoretext = '';
            $totalscore = 0;
            foreach ($sections as $section) {
                $txtsection = 'section_' . $section->section;
                if (!empty($config->$txtsection)) {
                    $sectionscoretext .= get_string('section', 'block_game') . ' ' . $section->section .
                        ': <strong>' . $config->$txtsection . 'pts</strong><br>';
                    $totalscore += (int) $config->$txtsection;
                }
            }
            if ($totalscore > 0) {
                $scoretext .= '<br>' . get_string('help_score_sections_text', 'block_game') . '<br>' . $sectionscoretext;
            }

            if (!empty($config->bonus_day)) {
                $scoretext .= '<br>' . get_string('help_bonus_day_text', 'block_game') . ' ' .
                    get_string('help_bonus_day_text_value', 'block_game') .
                    '<strong>' . $config->bonus_day . 'pts</strong>';
            }

            if (!empty($cfggame->bonus_badge)) {
                $scoretext .= '<br>' . get_string('help_bonus_badge', 'block_game') . ' ' .
                    get_string('help_bonus_badge_value', 'block_game') .
                    '<strong>' . $cfggame->bonus_badge . 'pts</strong>';
            }

            $this->features[] = [
                'key' => 'score',
                'image' => 'score.svg',
                'title' => get_string('help_score_titulo', 'block_game'),
                'text' => $scoretext,
            ];
        }

        if (!empty($config->show_rank)) {
            $ranktext = get_string('help_rank_text', 'block_game');
            $ranktext .= '<br>' . (
                empty($config->show_identity)
                    ? get_string('help_rank_list_restrict_text', 'block_game')
                    : get_string('help_rank_list_text', 'block_game')
            );
            $ranktext .= '<br>' . get_string('help_rank_criterion_text', 'block_game');

            $this->features[] = [
                'key' => 'rank',
                'image' => 'rank.svg',
                'title' => get_string('help_rank_titulo', 'block_game'),
                'text' => $ranktext,
            ];
        }

        if (!empty($config->show_level)) {
            $leveltext = get_string('help_level_text', 'block_game');
            $leveltext .= '<br>';

            if (!empty($config->level_number)) {
                for ($i = 1; $i <= $config->level_number; $i++) {
                    $key = 'level_up' . $i;
                    if (!empty($config->$key)) {
                        $leveltext .= get_string('label_level', 'block_game') . ' ' . $i . ': ' . $config->$key . 'pts<br>';
                    }
                }
            }

            $leveltext .= '<br>' . get_string('help_progress_level_text', 'block_game');
            $leveltext .= '<br><img src="' . (new \moodle_url('/blocks/game/pix/help_progress_level.svg')) .
                '" height="45" width="280"/>';

            $this->features[] = [
                'key' => 'level',
                'image' => 'level.svg',
                'title' => get_string('help_level_titulo', 'block_game'),
                'text' => $leveltext,
            ];
        }
    }
    /**
    * Exporta os dados da página para serem utilizados no template Mustache.
    *
    * @param \renderer_base $output Renderizador do Moodle.
    * @return array Dados formatados para o template.
    */
    public function export_for_template(renderer_base $output) {
        return [
            'title' => $this->title,
            'coursefullname' => $this->coursefullname,
            'isfrontpage' => $this->isfrontpage,
            'notconfigured' => $this->notconfigured,
            'features' => $this->features,
        ];
    }
}
