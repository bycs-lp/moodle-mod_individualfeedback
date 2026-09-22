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
 * Modified testcase for a unit test which covers any mbs hacks.
 *
 * For unit tests, that checks at least one mbs hack, the test class must
 * extend this class to:
 *
 * 1. indicate, that the testcase covers a hack.
 * 2. automatically setup the testcase to execute (and NOT skip) the hack.
 *
 * Please note that implemented hacks should NOT be executed, when core tests
 * are running.
 *
 * Quick coding guidelines for hacks:
 *
 * 1. Try to call hack code in one line and leave appropriate comment, for example:
 *
 * // +++ MBS-Hack (nersesov) : description
 * \mod_individualfeedback\hack\lib::dohack();
 * // --- MBS-Hack
 *
 * 2. Create a method in classes/hack/lib.php that extends core_hack to implement the hack:
 *
 * /**
 *  * Hack called from /path/to/file/calling the hack.
 *  *
 *  * Do the hack to fix this and that.
 *  *
 *  public static function doHack() {
 *
 *      if (self::is_running_core_test()) {
 *          return;
 *      }
 *
 *      execute hack code;
 *  }
 *
 * @package mod_individualfeedback
 * @copyright 2025 Andreas Wagner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

defined('MOODLE_INTERNAL') || die();

/**
 * Modified testcase for a unit test that covers core hacks.
 *
 * @package mod_individualfeedback
 * @copyright 2025 Andreas Wagner
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_hack_testcase extends \advanced_testcase {
    /**
     * Set the hack testcase flag to indicate we are running a test that covers
     * a core hack.
     *
     * Don't forget to call parent::setUp() in subclasses, when overriding this
     * method.
     */
    public function setUp(): void {
        parent::setUp();
        core_hack::start_hack_test();
    }

    /**
     * Reset the hack testcase flag to indicate we are leaving a test that covers
     * a core hack.
     *
     * Don't forget to call parent::tearDown() in subclasses, when overriding this
     * method.
     */
    public function tearDown(): void {
        core_hack::stop_hack_test();
        parent::tearDown();
    }

    /**
     * Recursively retrieves filenames from a directory.
     *
     * @param string $directory The path to the directory.
     * @param array $results An array that holds the found file paths.
     * @return array
     */
    protected function list_files_recursive(string $directory, array &$results = []): array {

        $files = scandir($directory);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->list_files_recursive($path, $results);
            } else {
                $results[] = $path;
            }
        }

        return $results;
    }

    /**
     * Determines a hash value as a checksum for the entire content of a directory.
     *
     * @param string $directory The path to the directory.
     * @return string The calculated hash value of the directory.
     * @throws Exception If the directory does not exist.
     */
    protected function get_directory_content_hash(string $directory): string {

        if (!is_dir($directory)) {
            throw new \Exception("The specified directory does not exist: $directory");
        }

        $hashcontext = hash_init('sha256');
        $files = $this->list_files_recursive($directory);
        sort($files);

        foreach ($files as $file) {
            if (is_file($file)) {
                hash_update($hashcontext, $file);

                $content = file_get_contents($file);
                if ($content !== false) {
                    hash_update($hashcontext, $content);
                }
            }
        }

        return hash_final($hashcontext);
    }
}
