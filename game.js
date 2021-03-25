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

// Global variables.
var game;
var achievements = [];
var phases = [];
var rewards = [];


// If you use folder in addition to the domain, add the folder name.
var url = 'https://moodle.etb.com.br/ead38';

// Load the user game.
function loadGame() {
    $(document).ready(function () {
        $.getJSON(url + '/blocks/game/game.php?op=load', function (result) {
            $.each(result, function (i, dados) {
                game = dados;
                rewards = [];
                if (game.rewards != 0 || game.rewards != '') {
                    for (i = 0; i < game.rewards.length; i++) {
                        rewards.push(parseInt(game.rewards[i]));
                    }
                } else {
                    rewards = new Array(0);
                }
                phases = [];
                if (game.phases != 0 || game.phases != '') {
                    for (i = 0; i < game.phases.length; i++) {
                        phases.push(parseInt(game.phases[i]));
                    }
                } else {
                    phases = new Array(0);
                }
                achievements = [];
                if (game.achievements != 0 || game.achievements != '') {
                    for (i = 0; i < game.achievements.length; i++) {
                        achievements.push(parseInt(game.achievements[i]));
                    }
                } else {
                    achievements = new Array(0);
                }

            });
        });
    });

}

// Add an achievement to the user.
function addAchievements(item) {
    achievements.push(item);
    $.post(url + '/blocks/game/game.php', {
        op: 'achievements', id: game.id, achievements: achievements.toString()
    }, function (result) {
        if (!result) {
            alert('Error adding achievement!');
        }
    });
    loadGame();
}
// Save vector of achievement.
function saveAchievements() {
    $.post(url + '/blocks/game/game.php', {
        op: 'achievements', id: game.id, achievements: achievements.toString()
    }, function (result) {
        if (!result) {
            alert('Error adding achievement!');
        }
    });
    loadGame();
}
// Remove an achievement from the user.
function delAchievements(item) {
    var i = achievements.indexOf(item);
    if (i != -1) {
        achievements.splice(i, 1);
    }
    $.post(url + '/blocks/game/game.php', {
        op: 'achievements', id: game.id, achievements: achievements.toString()
    }, function (result) {
        if (!result) {
            alert('Error removing achievement!');
        }
    });
    loadGame();
}

// Add a reward to the user.
function addReward(item) {
    rewards.push(item);
    $.post(url + '/blocks/game/game.php', {
        op: 'rewards', id: game.id, rewards: rewards.toString()
    }, function (result) {
        if (!result) {
            alert('Error adding reward!');
        }
    });
    loadGame();
}
// Save rewards of vector.
function saveReward() {
    $.post(url + '/blocks/game/game.php', {
        op: 'rewards', id: game.id, rewards: rewards.toString()
    }, function (result) {
        if (!result) {
            alert('Error adding reward!');
        }
    });
    loadGame();
}
// Remove a reward from the user.
function delReward(item) {
    var i = rewards.indexOf(item);
    if (i != -1) {
        rewards.splice(i, 1);
    }
    $.post(url + '/blocks/game/game.php', {
        op: 'rewards', id: game.id, rewards: rewards.toString()
    }, function (result) {
        if (!result) {
            alert('Error removing reward!');
        }
    });
    loadGame();
}
// Add a phase to the user.
function addPhase(item) {
    phases.push(item);
    $.post(url + '/blocks/game/game.php', {
        op: 'phases', id: game.id, phases: phases.toString()
    }, function (result) {
        if (!result) {
            alert('Error adding phase!');
        }
    });
    loadGame();
}
// Save phases of vector.
function savePhase() {
    $.post(url + '/blocks/game/game.php', {
        op: 'phases', id: game.id, phases: phases.toString()
    }, function (result) {
        if (!result) {
            alert('Erro ao adcionar fase!');
        }
    });
    loadGame();
}
// Remove a reward from the user.
function delPhase(item) {
    var i = phases.indexOf(item);
    if (i != -1) {
        phases.splice(i, 1);
    }
    $.post(url + '/blocks/game/game.php', {
        op: 'phases', id: game.id, phases: phases.toString()
    }, function (result) {
        if (!result) {
            alert('Error removing phase!');
        }
    });
    loadGame();
}
// Set user frame.
function setFrame(frame) {
    game.frame = frame;
    $.post(url + '/blocks/game/game.php', {
        op: 'frame', id: game.id, frame: game.frame
    }, function (result) {
        if (!result) {
            alert('Error setting frame!');
        }
    });
    loadGame();
}
