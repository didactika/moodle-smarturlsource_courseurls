<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace smarturlsource_courseurls;

use mod_smarturl\local\catalogue\source as source_contract;

/**
 * Exposes existing Moodle URL resources as a Smart URL catalogue.
 *
 * @package    smarturlsource_courseurls
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class source implements source_contract {
    /**
     * Returns the visible URL activities in the current course.
     *
     * @param \context_course $context Course context.
     * @return array
     */
    public function resources(\context_course $context): array {
        global $DB;
        $cms = array_filter(
            get_fast_modinfo($context->instanceid)->get_instances_of('url'),
            static fn($cm) => $cm->uservisible && has_capability('mod/url:view', $cm->context),
        );
        if (!$cms) {
            return [];
        }
        $ids = array_map(static fn($cm) => (int)$cm->instance, $cms);
        $urls = $DB->get_records_list('url', 'id', $ids, 'name ASC', 'id, name, externalurl');
        return array_values(array_map(static fn($url) => ['title' => $url->name, 'url' => $url->externalurl], $urls));
    }
}
