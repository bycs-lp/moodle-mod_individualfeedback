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
 * Trait with question-group layout helpers for the complete form (Hack H9).
 *
 * Injected into mod_individualfeedback_complete_form via a single `use`
 * statement (Wagner-approved trait injection pattern, see D9 in
 * review/PLAN.md). All members are net-new and additive.
 *
 * @package mod_individualfeedback
 * @copyright 2026 ISB Bayern / mebis
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Question-group layout helpers for the complete form.
 *
 * Builds a map of item id => zone ('start'|'inside'|'end') so question group
 * layout can be expressed with CSS classes on a flat sortable list (no
 * wrapping div), which keeps drag & drop reordering working inside groups.
 *
 * @package mod_individualfeedback
 * @copyright 2026 ISB Bayern / mebis
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait complete_form_group_trait {
    /** @var array|null item_id => 'start'|'inside'|'end' for question group zones (null until built) */
    protected $itemgroupzone = null;

    /**
     * Builds the item group zone map for question group layout.
     *
     * @param array $items All items of the individualfeedback in display order.
     * @return void
     */
    protected function build_item_group_zones(array $items): void {
        $this->itemgroupzone = [];
        $ingroup = false;
        foreach ($items as $item) {
            if ($item->typ === 'questiongroup') {
                $this->itemgroupzone[$item->id] = 'start';
                $ingroup = true;
            } else if ($item->typ === 'questiongroupend') {
                $this->itemgroupzone[$item->id] = 'end';
                $ingroup = false;
            } else if ($ingroup) {
                $this->itemgroupzone[$item->id] = 'inside';
            }
        }
    }

    /**
     * Returns the question group zone CSS class for an item (or empty string).
     *
     * Data-driven: yields a non-empty result only when question group items
     * exist in the individualfeedback, so core behaviour is unchanged unless
     * the fork-specific item types are used.
     *
     * @param \stdClass $item The item being rendered.
     * @return string Leading-space-prefixed CSS class or ''.
     */
    protected function get_group_zone_class(\stdClass $item): string {
        if ($this->itemgroupzone !== null && isset($this->itemgroupzone[$item->id])) {
            return ' individualfeedback_qgroup_zone_' . $this->itemgroupzone[$item->id];
        }
        return '';
    }
}
