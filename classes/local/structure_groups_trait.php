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
 * Trait with question-group helpers for mod_individualfeedback_structure.
 *
 * Injected into mod_individualfeedback_structure via a single `use`
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
 * Question-group helpers for the individualfeedback structure.
 *
 * @package mod_individualfeedback
 * @copyright 2026 ISB Bayern / mebis
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait structure_groups_trait {
    /** @var array|null items that belong to question groups (null until built) */
    protected $groupeditems = null;

    /**
     * Get all items inside question groups in this individualfeedback or template.
     *
     * Data-driven: returns a non-empty array only when questiongroup items
     * exist, so core behaviour is unchanged unless the fork-specific item
     * types are used.
     *
     * @return array of objects from individualfeedback_item with an additional attribute 'itemnr'
     */
    public function get_groups_and_items() {
        if ($this->groupeditems === null) {
            $this->groupeditems = [];
            if ($this->allitems === null) {
                $this->allitems = $this->get_items();
            }

            $ingroup = false;
            $idx = 1;
            foreach ($this->allitems as $id => $item) {
                if ($item->typ == 'questiongroup') {
                    $ingroup = true;
                }

                if ($ingroup) {
                    $this->groupeditems[$id] = $item;
                    $this->groupeditems[$id]->itemnr = $item->hasvalue ? ($idx++) : null;
                }

                if ($item->typ == 'questiongroupend') {
                    $ingroup = false;
                }
            }
        }

        return $this->groupeditems;
    }
}
