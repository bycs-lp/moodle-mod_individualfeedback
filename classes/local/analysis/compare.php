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
 * Compare analysis renderer.
 *
 * @package     mod_individualfeedback
 * @copyright   2025 Marcelo A. R. Schmitt
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\local\analysis;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use mod_individualfeedback_structure;

/**
 * Helper to render two feedback analyses side by side.
 */
class compare {
    /** @var bool */
    protected static $compactmode = false;

    /**
     * Render the comparison block for the given activity.
     *
     * @param \stdClass $individualfeedback Feedback activity record.
     * @param \cm_info|\stdClass $cm Course module instance.
     * @param int $id Course module id (kept for compatibility with existing signature).
     * @param moodle_url $url Current page URL.
     */
    public static function render(\stdClass $individualfeedback, $cm, int $id, moodle_url $url, bool $compact = false): void {
        global $OUTPUT;

        self::$compactmode = $compact;

        echo $individualfeedback->name;
        $individualfeedbackstructure = new mod_individualfeedback_structure($individualfeedback, $cm);

        \core\context\module::instance($cm->id);

        if (!$individualfeedbackstructure->can_view_analysis()) {
            throw new \moodle_exception('error');
        }

        $mygroupid = groups_get_activity_group($cm, true);
        groups_print_activity_menu($cm, $url);

        $summary = new \mod_individualfeedback\output\summary($individualfeedbackstructure, $mygroupid);
        echo $OUTPUT->render_from_template('mod_individualfeedback/summary', $summary->export_for_template($OUTPUT));

        $items = $individualfeedbackstructure->get_items(true);

        $checkanonymously = true;
        if ($mygroupid > 0 && $individualfeedback->anonymous == INDIVIDUALFEEDBACK_ANONYMOUS_YES) {
            $completedcount = $individualfeedbackstructure->count_completed_responses($mygroupid);
            if ($completedcount < INDIVIDUALFEEDBACK_MIN_ANONYMOUS_COUNT_IN_GROUP) {
                $checkanonymously = false;
            }
        }

        if ($checkanonymously) {
            foreach ($items as $item) {
                $itemobj = individualfeedback_get_item_class($item->typ);
                $printnumber = ($individualfeedback->autonumbering && $item->itemnr) ? ($item->itemnr . '.') : '';
                if (self::$compactmode && method_exists($itemobj, 'set_compact_mode')) {
                    $itemobj->set_compact_mode(true);
                }
                $itemobj->print_analysed($item, $printnumber, $mygroupid);
                if (self::$compactmode && method_exists($itemobj, 'set_compact_mode')) {
                    $itemobj->set_compact_mode(false);
                }
            }
        } else {
            echo $OUTPUT->heading_with_help(
                get_string('insufficient_responses_for_this_group', 'individualfeedback'),
                'insufficient_responses',
                'individualfeedback',
                '',
                '',
                3
            );
        }
    }
}
