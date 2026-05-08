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
 * Hack lib for mod_individualfeedback (template and item logic).
 *
 * @package     mod_individualfeedback
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;
/**
 * Hack lib for mod_individualfeedback (template and item logic).
 *
 * @package     mod_individualfeedback
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lib extends core_hack {
    /**
     * Hack function to add private templates to the template list.
     *
     * @param array $templates The existing templates, or null if not set.
     * @param string $onlyownorpublic Filter for templates: 'private' to include only user's private templates,
     * 'public' for public templates, or '' for all.
     *
     * @return array The modified list of templates including private ones if applicable.
     */
    public static function add_private_template_list(
        array $templates,
        string $onlyownorpublic
    ): array {
        global $DB, $USER;

        if (self::is_running_core_test()) {
            return $templates;
        }

        if ($onlyownorpublic === 'private') {
            return $DB->get_records(
                'individualfeedback_template',
                ['ispublic' => 2, 'userid' => $USER->id],
                'name'
            );
        }

        return $templates;
    }
}
