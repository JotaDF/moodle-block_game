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
 * Game block language strings
 *
 * @package    block_game
 * @copyright  2019 Jose Wilson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core\session\manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Block Game file function.
 *
 * @param stdClass $course
 * @param stdClass $birecordorcm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return boolean
 */
function block_game_pluginfile($course, $birecordorcm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG, $USER;

    $fs = get_file_storage();
    $filename = array_pop($args);

    if ($filearea === 'content') {
        if ($context->contextlevel != CONTEXT_BLOCK) {
            send_file_not_found();
        }

        if ($context->get_course_context(false)) {
            require_course_login($course);
        } else if ($CFG->forcelogin) {
            require_login();
        } else {
            $parentcontext = $context->get_parent_context();
            if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
                if (!core_course_category::get($parentcontext->instanceid, IGNORE_MISSING)) {
                    send_file_not_found();
                }
            } else if ($parentcontext->contextlevel === CONTEXT_USER && $parentcontext->instanceid != $USER->id) {
                send_file_not_found();
            }
        }

        $file = $fs->get_file($context->id, 'block_game', $filearea, 0, '/', $filename);
        if (!$file || $file->is_directory()) {
            send_file_not_found();
        }
    } else if ($filearea === 'imagens_avatar' || $filearea === 'imagens_levels') {
        $file = $fs->get_file($context->id, 'block_game', $filearea, 0, '/', $filename . '.svg');
        if (!$file || $file->is_directory()) {
            send_file_not_found();
        }
    } else {
        send_file_not_found();
    }

    manager::write_close();
    send_stored_file($file, null, 0, true, $options);

    return true;
}

/**
 * Return the game user
 *
 * @param stdClass $game
 * @return mixed
 */
function block_game_load_game($game) {
    global $DB;
    if (!empty($game->userid) && !empty($game->courseid)) {
        $sql = 'SELECT count(*) as total  FROM {block_game} WHERE courseid=? AND userid=?';
        $busca = $DB->get_record_sql($sql, array($game->courseid, $game->userid));
        if ($busca->total > 0) {
            $gamedb = $DB->get_record('block_game', array('courseid' => $game->courseid, 'userid' => $game->userid));
            return $gamedb;
        } else {
            $newgame = new stdClass();
            $newgame->courseid = $game->courseid;
            $newgame->userid = $game->userid;
            $newgame->avatar = block_game_get_avatar_user($game->userid);
            $newgame->score = 0;
            $newgame->score_activities = 0;
            $newgame->score_module_completed = 0;
            $newgame->score_bonus_day = 0;
            $newgame->score_badges = 0;
            $newgame->score_section = 0;
            $newgame->level = 0;
            $newgame->ranking = 0;
            $newgame->achievements = "";
            $newgame->rewards = "";
            $newgame->phases = "";
            $newgame->badges = "";
            $newgame->frame = "";
            $newgame->bonus_day = null;

            $lastinsertid = $DB->insert_record('block_game', $newgame);
            $newgame->id = $lastinsertid;

            return $newgame;
        }
    }
    return false;
}

/**
 * Return the games user
 *
 * @param int $userid
 * @return mixed
 */
function block_game_get_games_user($userid) {
    global $DB;
    if (!empty($userid)) {
        $games = $DB->get_records_sql('SELECT * FROM {block_game} WHERE userid=? ORDER BY courseid DESC', array($userid));
        return $games;
    }
    return false;
}

/**
 * Return update avatar user
 *
 * @param stdClass $game
 * @return boolean
 */
function block_game_update_avatar_game($game) {
    global $DB;

    if (!empty($game->userid) && !empty($game->avatar)) {

        $DB->execute("UPDATE {block_game} SET avatar=? WHERE userid=?", array($game->avatar, $game->userid));

        return true;
    }
    return false;
}

/**
 * Return update score user
 *
 * @param stdClass $game
 * @return boolean
 */
function block_game_update_score_game($game) {
    global $DB;

    if (!empty($game->id) && !empty($game->score)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->score = $game->score;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

/**
 * Return update level user
 *
 * @param stdClass $game
 * @return boolean
 */
function block_game_update_level_game($game) {
    global $DB;

    if (!empty($game->id) && !empty($game->level)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->level = $game->level;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

/**
 * Return update achievements user
 *
 * @param stdClass $game
 * @return boolean
 */
function block_game_update_achievements_game($game) {
    global $DB;

    if (!empty($game->id)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->achievements = $game->achievements;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

/**
 * Return update rewards user
 *
 * @param stdClass $game
 * @return boolean
 */
function block_game_update_rewards_game($game) {
    global $DB;

    if (!empty($game->id)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->rewards = $game->rewards;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

/**
 * Return update phases user
 *
 * @param stdClass $game
 * @return boolean
 */
function block_game_update_phases_game($game) {
    global $DB;

    if (!empty($game->id)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->phases = $game->phases;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

/**
 * Return update badge user
 *
 * @param stdClass $game
 * @return boolean
 */
function block_game_update_badges_game($game) {
    global $DB;

    if (!empty($game->id)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->badges = $game->badges;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

/**
 * Return update frame user
 *
 * @param stdClass $game
 * @return boolean
 */
function block_game_update_frame_game($game) {
    global $DB;

    if (!empty($game->id)) {

        $savegame = new stdClass();
        $savegame->id = $game->id;
        $savegame->frame = $game->frame;

        $DB->update_record('block_game', $savegame);

        return true;
    }
    return false;
}

/**
 * Reset bonus of day course
 *
 * @param int $courseid
 * @return boolean
 */
function block_game_reset_points_game($courseid) {
    global $DB;
    if (!empty($courseid)) {
        $sql = "UPDATE {block_game} SET score_bonus_day=0, score=0, score_activities=0,"
                . " score_section=0, score_badges=0  WHERE courseid=" . $courseid;
        $DB->execute($sql);
        return true;
    }
    return false;
}

/**
 * Return update bonus of day user
 *
 * @param stdClass $game
 * @param int $bonus
 * @return boolean
 */
function block_game_bonus_of_day($game, $bonus) {
    global $DB;
    if (!empty($game->id)) {
        $sql = 'SELECT CURRENT_DATE as hoje, bonus_day, score_bonus_day'
                . '  FROM {block_game} WHERE courseid=? AND userid=?';
        $busca = $DB->get_record_sql($sql, array($game->courseid, $game->userid));
        if ($busca->bonus_day == null || $busca->bonus_day < $busca->hoje) {
            $game->score_bonus_day = ((int) $game->score_bonus_day + (int) $bonus);
            $sqlupdate = "UPDATE {block_game} SET score_bonus_day=?, bonus_day=?  WHERE id=?";
            $DB->execute($sqlupdate, array((int) $game->score_bonus_day, $busca->hoje, $game->id));
        }
        return ((int) $game->score + (int) $bonus);
    }
    return false;
}

/**
 * Return update score activities user
 *
 * @param stdClass $game
 * @return boolean
 */
function block_game_score_activities($game) {
    global $DB;

    if (!empty($game->id)) {

        $sql = "SELECT SUM(COALESCE(g.finalgrade,0)) as score_activities"
                . " FROM {grade_grades} g INNER JOIN {grade_items} i ON g.itemid=i.id"
                . " WHERE i.courseid=? AND i.itemtype='mod' AND g.userid=?";
        $busca = $DB->get_record_sql($sql, array($game->courseid, $game->userid));

        if ($busca->score_activities == "" || empty($busca->score_activities)) {
            $game->score_activities = 0;
        } else {
            $game->score_activities = (int) $busca->score_activities;
        }
        $DB->execute("UPDATE {block_game} SET score_activities=? WHERE id=?", array((int) $game->score_activities, $game->id));

        return true;
    }
    return false;
}

/**
 * Return update game user
 *
 * @param stdClass $game
 * @return boolean
 */
function block_game_no_score_activities($game) {
    global $DB;
    if (!empty($game->id)) {
        $DB->execute("UPDATE {block_game} SET score_activities=0 WHERE id=?", array($game->id));
        return true;
    }
    return false;
}

/**
 * Return update game user
 *
 * @param stdClass $game
 * @param int $value
 * @return mixed
 */
function block_game_score_badge($game, $value) {
    global $DB;

    $badges = array();
    if (!empty($game->userid)) {

        $sql = "SELECT cc.userid, cc.course, COALESCE(cc.timecompleted, 0) timecompleted
                FROM {course_completions} cc
                WHERE cc.userid = ?
                AND timecompleted<>0";

        $rs = $DB->get_records_sql($sql, array($game->userid));
        $nbadges = 0;
        foreach ($rs as $c) {
            $badges[$nbadges] = $c->course;
            $nbadges++;
        }

        $game->badges = "" . implode(",", $badges) . "";
        $game->score_badges = ($nbadges * $value);
        $sql = 'UPDATE {block_game} SET badges=?,score_badges=? WHERE userid=? AND courseid=?';
        $DB->execute($sql, array($game->badges, (int) $game->score_badges, $game->userid, 1));

        return $game;
    }
    return $game;
}

/**
 * Return update ranking game
 *
 * @param stdClass $game
 * @param int $groupid
 * @return mixed
 */
function block_game_ranking($game, $groupid = 0) {
    global $DB;

    if (!empty($game->id)) {
        if ($game->courseid == SITEID) {

            $sql = 'SELECT g.userid, u.firstname,SUM(g.score) sum_score,'
                    . ' SUM(COALESCE(g.score_activities, 0)) sum_score_activities,'
                    . ' SUM(COALESCE(g.score_module_completed, 0)) sum_score_module_completed,'
                    . ' SUM(COALESCE(g.score_bonus_day, 0)) sum_score_bonus_day,'
                    . ' SUM(COALESCE(g.score_badges, 0)) sum_score_badges,'
                    . ' SUM(COALESCE(g.score_section, 0)) sum_score_section,'
                    . ' (SUM(score)+SUM(COALESCE(g.score_activities, 0))'
                    . '+SUM(COALESCE(g.score_module_completed, 0))'
                    . '+SUM(COALESCE(g.score_bonus_day, 0))'
                    . '+SUM(COALESCE(g.score_badges, 0))'
                    . '+SUM(COALESCE(g.score_section, 0))) pt'
                    . ' FROM {block_game} g, {user} u'
                    . ' WHERE u.id=g.userid GROUP BY g.userid, u.firstname'
                    . ' ORDER BY pt DESC,sum_score_badges DESC,'
                    . 'sum_score_activities DESC,sum_score_module_completed DESC,sum_score DESC, g.userid ASC';

            $ranking = $DB->get_records_sql($sql);
            $poisicao = 1;
            foreach ($ranking as $rs) {
                if ($rs->userid == $game->userid) {
                    $game->ranking = $poisicao;
                    $game->score = $rs->sum_score;
                    $game->score_bonus_day = $rs->sum_score_bonus_day;
                    $game->score_activities = $rs->sum_score_activities;
                    $game->score_module_completed = $rs->sum_score_module_completed;
                    $game->score_section = $rs->sum_score_section;
                    break;
                }
                $poisicao++;
            }
        } else {
            $wheregroup = "";
            if ($groupid > 0) {
                $wheregroup = " AND u.id IN(SELECT userid FROM {groups_members} WHERE groupid=$groupid) ";
            }

            $sql = 'SELECT g.userid, u.firstname,SUM(g.score) sum_score,'
                    . ' SUM(COALESCE(g.score_activities, 0)) sum_score_activities,'
                    . ' SUM(COALESCE(g.score_module_completed, 0)) sum_score_module_completed,'
                    . ' SUM(COALESCE(g.score_bonus_day, 0)) sum_score_bonus_day,'
                    . ' SUM(COALESCE(g.score_section, 0)) sum_score_section,'
                    . ' (SUM(score)+SUM(COALESCE(score_activities, 0))'
                    . '+SUM(COALESCE(g.score_module_completed, 0))'
                    . '+SUM(COALESCE(g.score_bonus_day, 0))'
                    . '+SUM(COALESCE(g.score_section, 0))) pt'
                    . ' FROM {role_assignments} rs '
                    . ' INNER JOIN {user} u ON u.id=rs.userid '
                    . ' INNER JOIN {context} e ON rs.contextid=e.id '
                    . ' INNER JOIN {block_game} g ON g.userid=u.id '
                    . ' WHERE e.contextlevel=50 AND rs.roleid<6 ' . $wheregroup
                    . ' AND g.courseid=e.instanceid  AND e.instanceid=? '
                    . ' GROUP BY g.userid, u.firstname ORDER BY pt DESC,'
                    . ' sum_score_activities DESC,sum_score_module_completed DESC,sum_score DESC, g.userid ASC';

            $ranking = $DB->get_records_sql($sql, array($game->courseid));
            $poisicao = 1;
            foreach ($ranking as $rs) {
                if ($rs->userid == $game->userid) {
                    $game->ranking = $poisicao;
                    break;
                }
                $poisicao++;
            }
        }
        $DB->execute("UPDATE {block_game} SET ranking=? WHERE id=?", array($game->ranking, $game->id));
    }
    return $game;
}

/**
 * Return ranking list.
 *
 * @param int $courseid
 * @param int $groupid
 * @return mixed
 */
function block_game_rank_list($courseid, $groupid = 0) {
    global $DB;

    if (!empty($courseid)) {
        if ($courseid == SITEID) {

            $sql = 'SELECT g.userid, u.firstname, u.lastname ,SUM(g.score) sum_score,'
                    . ' SUM(COALESCE(g.score_activities, 0)) sum_score_activities,'
                    . ' SUM(COALESCE(g.score_module_completed, 0)) sum_score_module_completed,'
                    . ' SUM(COALESCE(g.score_bonus_day, 0)) sum_score_bonus_day,'
                    . ' SUM(COALESCE(g.score_badges, 0)) sum_score_badges,'
                    . ' SUM(COALESCE(g.score_section, 0)) sum_score_section,'
                    . ' (SUM(score)+SUM(COALESCE(g.score_activities, 0))'
                    . '+SUM(COALESCE(g.score_module_completed, 0))'
                    . '+SUM(COALESCE(g.score_bonus_day, 0))'
                    . '+SUM(COALESCE(g.score_badges, 0))'
                    . '+SUM(COALESCE(g.score_section, 0))) pt'
                    . ' FROM {block_game} g, {user} u'
                    . ' WHERE u.id=g.userid GROUP BY g.userid, u.firstname, u.lastname '
                    . 'ORDER BY pt DESC,sum_score_badges DESC,sum_score_activities'
                    . ' DESC,sum_score_module_completed DESC,sum_score DESC, g.userid ASC';

            $ranking = $DB->get_records_sql($sql);
            return $ranking;
        } else {
            $wheregroup = "";
            if ($groupid > 0) {
                $wheregroup = " AND u.id IN(SELECT userid FROM {groups_members} WHERE groupid=$groupid) ";
            }

            $sql = "SELECT g.userid, u.firstname, u.lastname, g.avatar,SUM(g.score) sum_score,"
                    . " SUM(COALESCE(g.score_activities, 0)) sum_score_activities,"
                    . " SUM(COALESCE(g.score_module_completed, 0)) sum_score_module_completed,"
                    . " SUM(COALESCE(g.score_bonus_day, 0)) sum_score_bonus_day,"
                    . " SUM(COALESCE(g.score_section, 0)) sum_score_section,"
                    . " (SUM(score)+SUM(COALESCE(g.score_activities, 0))"
                    . "+SUM(COALESCE(g.score_module_completed, 0))"
                    . "+SUM(COALESCE(g.score_bonus_day, 0))"
                    . "+SUM(COALESCE(g.score_section, 0))) pt"
                    . " FROM {role_assignments} rs "
                    . " INNER JOIN {user} u ON u.id=rs.userid "
                    . " INNER JOIN {context} e ON rs.contextid=e.id "
                    . " INNER JOIN {block_game} g ON g.userid=u.id "
                    . " INNER JOIN {role} r ON r.id=rs.roleid"
                    . " WHERE e.contextlevel=50 AND r.archetype = 'student'  " . $wheregroup
                    . " AND g.courseid=e.instanceid  AND e.instanceid=? "
                    . " GROUP BY g.userid, u.firstname, u.lastname, g.avatar ORDER BY pt DESC,"
                    . " sum_score_activities DESC,sum_score_module_completed DESC,sum_score DESC, g.userid ASC";

            $ranking = $DB->get_records_sql($sql, array($courseid));
            return $ranking;
        }
    }
    return false;
}

/**
 * Return list ranking group game
 *
 * @param int $courseid
 * @return mixed
 */
function block_game_ranking_group($courseid) {
    global $DB;

    if (!empty($courseid)) {
        if ($courseid != SITEID) {
            $sql = 'SELECT g.id, g.name, COUNT(m.id) AS members,'
                    . ' SUM(bg.score)+SUM(COALESCE(bg.score_bonus_day, 0))'
                    . '+SUM(COALESCE(bg.score_activities, 0))'
                    . '+SUM(COALESCE(bg.score_module_completed, 0))'
                    . '+SUM(COALESCE(bg.score_section, 0)) AS pt'
                    . ' FROM {groups_members} m, {groups} g, {block_game} bg'
                    . ' WHERE g.id=m.groupid'
                    . ' AND bg.userid=m.userid'
                    . ' AND bg.courseid=g.courseid AND g.courseid=?'
                    . ' GROUP BY g.id, g.name ORDER BY pt DESC';

            $rs = $DB->get_records_sql($sql, array($courseid));
            return $rs;
        }
        return false;
    }
    return false;
}

/**
 * Return list ranking group game of media
 *
 * @param int $courseid
 * @return mixed
 */
function block_game_ranking_group_md($courseid) {
    global $DB;

    if (!empty($courseid)) {
        if ($courseid != SITEID) {

            $sql = 'SELECT g.id, g.name, COUNT(m.id) AS members,'
                    . ' SUM(bg.score)+SUM(COALESCE(bg.score_bonus_day, 0))'
                    . '+SUM(COALESCE(bg.score_activities, 0))'
                    . '+SUM(COALESCE(bg.score_module_completed, 0))'
                    . '+SUM(COALESCE(bg.score_section, 0)) AS pt'
                    . ' FROM {groups_members} m, {groups} g, {block_game} bg'
                    . ' WHERE g.id=m.groupid'
                    . ' AND bg.userid=m.userid'
                    . ' AND bg.courseid=g.courseid AND g.courseid=?'
                    . ' GROUP BY g.id, g.name ORDER BY pt DESC';

            $rs = $DB->get_records_sql($sql, array($courseid));

            $grups = array();
            foreach ($rs as $group) {
                $grupo = new stdClass();
                $grupo->id = $group->id;
                $grupo->name = $group->name;
                $grupo->members = $group->members;
                $grupo->pt = $group->pt;
                $grupo->md = ($group->pt / $group->members);
                $grups[] = $grupo;
            }

            usort($grups, function( $a, $b ) {
                if ($a->md == $b->md) {
                    return 0;
                }
                if ($a->md > $b->md) {
                    return -1;
                }
                return 1;
            });
            return $grups;
        }
        return false;
    }
    return false;
}

/**
 * Return set level user
 *
 * @param stdClass $game
 * @param int $levelup
 * @param int $levelnumber
 * @return stdClass $game
 */
function block_game_set_level($game, $levelup, $levelnumber) {
    global $DB;

    if (!empty($game->id)) {
        $pt = $game->score + $game->score_bonus_day + $game->score_activities
                + $game->score_module_completed + $game->score_section;
        if ($game->courseid == SITEID) {
            $pt = $game->score + $game->score_bonus_day + $game->score_activities
                    + $game->score_module_completed + $game->score_badges + $game->score_section;
        }
        if (block_game_sets_level($pt, $levelup) >= $levelnumber) {
            $level = $levelnumber;
        } else {
            $level = block_game_sets_level($pt, $levelup);
        }
        $game->level = $level;
    }
    $DB->execute("UPDATE {block_game} SET level=? WHERE id=?", array($game->level, $game->id));
    return $game;
}

/**
 * Return set level user
 *
 * @param int $scorefull
 * @param array $levelup
 * @return int
 */
function block_game_sets_level($scorefull, $levelup) {
    $level = 0;
    foreach ($levelup as $levelvalue) {
        if ($scorefull >= $levelvalue) {
            $level++;
        }
    }
    return $level;
}

/**
 * Return number of players of course.
 *
 * @param int $courseid
 * @param int $groupid
 * @return int
 */
function block_game_get_players($courseid, $groupid = 0) {
    global $DB;
    if (!empty($courseid)) {
        if ($courseid == SITEID) {
            $sql = 'SELECT count(*) as total FROM {user} '
                    . 'WHERE confirmed=1 AND deleted=0 AND suspended=0 AND id > 1';
            $busca = $DB->get_record_sql($sql);
            return $busca->total;
        } else {
            $wheregroup = "";
            if ($groupid > 0) {
                $wheregroup = " AND u.id IN(SELECT userid FROM {groups_members} WHERE groupid=$groupid) ";
            }
            $sql = 'SELECT count(*) as total FROM {role_assignments} rs,'
                    . ' {user} u, {context} e WHERE u.id=rs.userid AND rs.contextid=e.id '
                    . 'AND e.contextlevel=50 ' . $wheregroup . ' AND e.instanceid=?';
            $busca = $DB->get_record_sql($sql, array($courseid));
            return $busca->total;
        }
    }
    return false;
}

/**
 * Return number not players of course.
 *
 * @param int $courseid
 * @param int $groupid
 * @return int
 */
function block_game_get_no_players($courseid, $groupid = 0) {
    global $DB;
    if (!empty($courseid)) {
        if ($courseid == SITEID) {
            $sql = 'SELECT count(*) as total FROM {user} '
                    . 'WHERE confirmed=1 AND deleted=0 AND suspended=0 AND id > 1 '
                    . 'AND id NOT IN(SELECT userid FROM {block_game})';
            $busca = $DB->get_record_sql($sql);
            return $busca->total;
        } else {
            $wheregroup = "";
            if ($groupid > 0) {
                $wheregroup = " AND u.id IN(SELECT userid FROM {groups_members} WHERE groupid=$groupid) ";
            }
            $sql = 'SELECT count(*) as total FROM {role_assignments} rs, {user} u, {context} e '
                    . 'WHERE u.id=rs.userid AND rs.contextid=e.id AND e.contextlevel=50  '
                    . $wheregroup . ' AND e.instanceid=?'
                    . ' AND u.id NOT IN(SELECT userid FROM {block_game})';
            $busca = $DB->get_record_sql($sql, array($courseid));
            return $busca->total;
        }
    }
    return false;
}

/**
 * Get the avatar the user.
 *
 * @param int $userid
 * @return int the total time spent in seconds
 */
function block_game_get_avatar_user($userid) {
    global $DB;
    if (!empty($userid)) {
        $sql = 'SELECT MAX(avatar) avatar FROM {block_game} '
                . 'WHERE userid=' . $userid . ' AND avatar > 0';
        $busca = $DB->get_record_sql($sql);
        if (isset($busca->avatar)) {
            return $busca->avatar;
        } else {
            return 0;
        }
    }
    return 0;
}

/**
 * Return modules list.
 *
 * @param int $courseid
 * @return mixed
 */
function block_game_get_modules_tracking($courseid) {
    global $DB;

    if (!empty($courseid)) {
        if ($courseid != SITEID) {
            $sql = 'SELECT DISTINCT m.id, m.name as module '
                    . ' FROM {modules} m, {course_modules} cm '
                    . ' WHERE cm.module=m.id AND cm.completion > 0 AND deletioninprogress=0 AND cm.course=? '
                    . 'ORDER BY m.id';
            $modules = $DB->get_records_sql($sql, array($courseid));
            return $modules;
        }
    }
    return false;
}

/**
 * Validates the font size that was entered by the user.
 *
 * @param string $userid the font size integer to validate.
 * @param string $courseid the font size integer to validate.
 * @param string $sectionid the font size integer to validate.
 * @return true|false
 */
function block_game_is_check_section($userid, $courseid, $sectionid) {
    global $DB; // Check section.
    $atvok = $DB->get_record_sql("SELECT COUNT(c.id) AS total "
            . "FROM {course_modules_completion} c "
            . "INNER JOIN {course_modules} m ON c.coursemoduleid = m.id "
            . "WHERE c.userid=" . $userid . " AND m.course=" . $courseid
            . " AND m.section=" . $sectionid . " AND m.completion > 0 "
            . "AND c.completionstate > 0 AND m.deletioninprogress = 0");
    $atv = $DB->get_record_sql("SELECT COUNT(id) AS total FROM {course_modules} WHERE course="
            . $courseid . " AND section=" . $sectionid
            . " AND completion > 0 AND deletioninprogress = 0");
    if ($atvok->total == $atv->total && $atv->total != 0) {
        return true;
    }
    return false;
}

/**
 * get sections of course.
 *
 * @param string $courseid the font size integer to validate.
 * @return mixed
 */
function block_game_get_sections_course($courseid) {
    global $DB; // Check section.
    if (!empty($courseid)) {
        if ($courseid != SITEID) {
            $sql = 'SELECT * FROM {course_sections} WHERE course = ?'
                    . 'ORDER BY section';
            $sections = $DB->get_records_sql($sql, array($courseid));
            return $sections;
        }
    }
    return false;
}

/**
 * Return update score sections user
 *
 * @param stdClass $game
 * @param int $scoresections
 * @return boolean
 */
function block_game_score_section($game, $scoresections) {
    global $DB;
    if (!empty($game->id)) {
        $DB->execute("UPDATE {block_game} SET score_section=?  WHERE id=?", array((int) $scoresections, $game->id));
        return true;
    }
    return false;
}

/**
 * Return update score modules completed user
 *
 * @param stdClass $game
 * @param int $scoremodcompleted
 * @return boolean
 */
function block_game_score_mod_completed($game, $scoremodcompleted) {
    global $DB;
    if (!empty($game->id)) {
        $DB->execute("UPDATE {block_game} SET score_module_completed=?  WHERE id=?", array((int) $scoremodcompleted, $game->id));
        return true;
    }
    return false;
}

/**
 * Get if student the user.
 *
 * @param int $userid
 * @param int $courseid
 * @return int the total time spent in seconds
 */
function block_game_is_student_user($userid, $courseid) {
    global $DB;
    if ($courseid != SITEID) {
        $sql = 'SELECT count(*) as total '
                . " FROM {role_assignments} rs "
                . " INNER JOIN {user} u ON u.id=rs.userid "
                . " INNER JOIN {context} e ON rs.contextid=e.id "
                . " INNER JOIN {role} r ON r.id=rs.roleid "
                . " WHERE e.contextlevel=50 AND r.archetype = 'student' "
                . " AND e.instanceid=? "
                . " AND u.id=?";
        $busca = $DB->get_record_sql($sql, array($courseid, $userid));
        return $busca->total;
    }
    return 0;
}

/**
 * Return update score sections user
 *
 * @param stdClass $game
 * @param boolean $scoreok
 * @param boolean $showlevel
 * @param boolean $scoreactivities
 * @param array $atvscheck
 * @param stdClass $cfggame
 * @return stdClass $game
 */
function block_game_process_game($game, $scoreok, $showlevel, $scoreactivities, $atvscheck, $cfggame) {
    global $USER, $COURSE;
    $levelnumber = 0;
    // Config level up.
    if ($showlevel && isset($game->config->show_level)) {
        $levelnumber = (int) $game->config->level_number;
        $levelup = array();
        for ($i = 1; $i <= $game->config->level_number; $i++) {
            $xlevel = 'level_up' . $i;
            $levelup[] = (int) $game->config->$xlevel;
        }
    }
    if ($game->courseid != SITEID) {
        // Sum score sections complete.
        $sections = block_game_get_sections_course($COURSE->id);
        $scoresections = 0;
        foreach ($sections as $section) {
            $txtsection = "section_" . $section->section;
            if (block_game_is_check_section($USER->id, $COURSE->id, $section->id)) {
                if (isset($game->config->$txtsection)) {
                    $scoresections += (int) $game->config->$txtsection;
                }
            }
        }

        // Sum score modules complete.
         $scoremodcompleted = 0;
        foreach ($atvscheck as $activity) {
            $atvcheck = 'atv' . $activity['id'];
            if (isset($game->config->$atvcheck) && $game->config->$atvcheck > 0) {
                if (block_game_is_completed_module($USER->id, $COURSE->id, $activity['id'])) {
                    $scoremodcompleted += (int) $game->config->$atvcheck;
                }
            }
        }

        if ($scoreok) {
            block_game_score_section($game, $scoresections);
            $game->score_section = $scoresections;

            block_game_score_mod_completed($game, $scoremodcompleted);
            $game->score_module_completed = $scoremodcompleted;
        }
    }
    // Bonus of day.
    $addbonusday = 0;
    if (isset($game->config->bonus_day)) {
        $addbonusday = $game->config->bonus_day;
    }
    if ($addbonusday > 0 && $scoreok) {
        block_game_bonus_of_day($game, $addbonusday);
    }
    // Bonus of badge.
    if (isset($cfggame->bonus_badge)) {
        $bonusbadge = $cfggame->bonus_badge;
        if ($scoreok) {
            $game = block_game_score_badge($game, $bonusbadge);
        }
    }
    // Search user group.
    $groupid = 0;
    if ($COURSE->groupmode == 1 || $COURSE->groupmode == 2) {
        $groups = \groups_get_all_groups($COURSE->id, $USER->id);
        foreach ($groups as $group) {
            $groupid = $group->id;
        }
    }
    $game->groupid = $groupid;
    // Pontuation activities.
    if ($scoreactivities && $scoreok) {
        block_game_score_activities($game);
        $game = block_game_ranking($game, $groupid);
        if ($showlevel && isset($game->config->show_level)) {
            $game = block_game_set_level($game, $levelup, $levelnumber);
        }
    } else {
        block_game_no_score_activities($game);
        $game = block_game_ranking($game, $groupid);
        if ($showlevel && isset($game->config->show_level)) {
            $game = block_game_set_level($game, $levelup, $levelnumber);
        }
    }
    // Calculate score full.
    $scorefull = (int) ($game->score + $game->score_bonus_day + $game->score_activities
            + $game->score_module_completed + $game->score_badges + $game->score_section);
    if ($COURSE->id != SITEID) {
        $scorefull = (int) ($game->score + $game->score_bonus_day
                            + $game->score_activities + $game->score_module_completed + $game->score_section);
    }
    $game->scorefull = $scorefull;
    // Calculate percentage to the next level.
    $percent = 0;
    $nextlevel = $game->level + 1;
    if ($nextlevel <= $levelnumber) {
        $percent = 0;
        if ($scorefull > 0) {
            $percent = ($scorefull * 100) / $levelup[$game->level];
        }
    }
    $game->percent = $percent;

    return $game;
}

/**
 * Get the last block configuration.
 *
 * @param int $courseid
 * @return mixed the config
 */
function block_game_get_config_block($courseid) {
    global $DB;
    $coursecontext = \context_course::instance($courseid);
    $blockrecords = $DB->get_records('block_instances', array('blockname' => 'game', 'parentcontextid' => $coursecontext->id));
    foreach ($blockrecords as $b) {
        $blockinstance = \block_instance('game', $b);
    }
    if (isset($blockinstance->config)) {
        return $blockinstance->config;
    }
    return false;
}

/**
 * Get the last block configuration.
 *
 * @param stdClass $game
 * @return stdClass $game
 */
function block_game_get_percente_level($game) {
    if ($game->courseid == SITEID) {
        $game->config = get_config('block_game');
    } else {
        $game->config = block_game_get_config_block($game->courseid);
    }

    $levelnumber = 0;
    // Config level up.
    if (isset($game->config->show_level) && $game->config->show_level == 1) {
        $levelnumber = (int) $game->config->level_number;
        $levelup = array();
        for ($i = 1; $i <= $game->config->level_number; $i++) {
            $xlevel = 'level_up' . $i;
            $levelup[] = (int) $game->config->$xlevel;
        }
    }
    // Calculate score full.
    $scorefull = (int) ($game->score + $game->score_bonus_day + $game->score_activities
            + $game->score_module_completed + $game->score_badges + $game->score_section);
    if ($game->courseid != SITEID) {
        $scorefull = (int) ($game->score + $game->score_bonus_day + $game->score_activities
                + $game->score_module_completed + $game->score_section);
    }
    $game->scorefull = $scorefull;
    // Calculate percentage to the next level.
    $percent = 0;
    $nextlevel = $game->level + 1;
    if ($nextlevel <= $levelnumber) {
        $percent = 0;
        if ($scorefull > 0) {
            $percent = ($scorefull * 100) / $levelup[$game->level];
        }
    }
    $game->percent = $percent;
    return $game;
}

/**
 * Reaction image
 *
 * @param int $contextid
 * @param string $filearea
 * @param string $react
 * @return string
 */
function block_game_pix_url($contextid, $filearea, $react) {

    return strval(moodle_url::make_pluginfile_url(
                    $contextid, 'block_game', $filearea, 0, '/', $react)
    );
}

/**
 * Given an array with a file path, it returns the itemid and the filepath for the defined filearea.
 *
 * @param string $filearea
 * @param array $args
 *
 * @return array
 */
function block_game_view_get_path_from_pluginfile($filearea, $args) {
    array_shift($args);

    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    return [
        'itemid' => 0,
        'filepath' => $filepath,
    ];
}

/**
 * Returns the activities in current course
 *
 * @param int $courseid ID of the course
 * @return array Activities with completion settings in the course
 * @throws coding_exception
 * @throws moodle_exception
 */
function block_game_get_course_activities($courseid) {
    $modinfo = get_fast_modinfo($courseid, -1);
    $sections = $modinfo->get_sections();
    $activities = array();
    $types = array();
    $ids = array();
    foreach ($modinfo->instances as $module => $instances) {
        $modulename = get_string('pluginname', $module);
        foreach ($instances as $cm) {
            if ($module != 'label') {
                if (!in_array($module, $types)) {
                    array_push($types, $module);
                }
                array_push($ids, $cm->id);
                $activities[] = array(
                    'type' => $module,
                    'modulename' => $modulename,
                    'id' => $cm->id,
                    'instance' => $cm->instance,
                    'name' => $cm->name,
                    'expected' => $cm->completionexpected,
                    'section' => $cm->sectionnum,
                    'position' => array_search($cm->id, $sections[$cm->sectionnum]),
                    'url' => method_exists($cm->url, 'out') ? $cm->url->out() : '',
                    'context' => $cm->context,
                    'icon' => $cm->get_icon_url(),
                    'available' => $cm->available,
                );
            }
        }
    }
    usort($activities, 'block_game_compare_activities');
    return array('activities' => $activities, 'types' => $types, 'ids' => $ids);
}

/**
 * Used to compare two activities/resources based on order on course page
 *
 * @param array $a array of event information
 * @param array $b array of event information
 * @return mixed <0, 0 or >0 depending on order of activities/resources on course page
 */
function block_game_compare_activities($a, $b) {
    if ($a['section'] != $b['section']) {
        return $a['section'] - $b['section'];
    } else {
        return $a['position'] - $b['position'];
    }
}

/**
 * Validates the font size that was entered by the user.
 *
 * @param string $userid the font size integer to validate.
 * @param string $courseid the font size integer to validate.
 * @param string $cmid the font size integer to validate.
 * @return true|false
 */
function block_game_is_completed_module($userid, $courseid, $cmid) {
    global $DB;
    $countok = $DB->get_record_sql("SELECT COUNT(c.id) AS total FROM {course_modules_completion} c"
            . " INNER JOIN {course_modules} m ON c.coursemoduleid = m.id WHERE c.userid="
            . $userid . " AND m.course=" . $courseid . " AND c.coursemoduleid=" . $cmid .
            " AND m.completion > 0 AND c.completionstate > 0 AND m.deletioninprogress = 0");

    if ($countok->total > 0) {
        return true;
    }
    return false;
}

/**
 * Validates module is visibled.
 *
 * @param string $courseid id course.
 * @param string $cmid id module.
 * @return true|false
 */
function block_game_is_visibled_module($courseid, $cmid) {
    global $DB;
    $countatv = $DB->get_record_sql("SELECT COUNT(id) AS total FROM {course_modules} WHERE course="
            . $courseid . " AND id=" . $cmid . " AND completion > 0 AND deletioninprogress = 0");
    if ($countatv->total > 0) {
        return true;
    }
    return false;
}
