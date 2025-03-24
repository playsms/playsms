<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

// Configuration settings for the code review plugin
$code_review_enabled = true;
$code_review_criteria = [
    'coding_standards' => true,
    'security' => true,
    'performance' => true,
    'readability' => true,
    'maintainability' => true,
];
$code_review_notification_email = 'code-review@example.com';
$code_review_auto_assign = true;
$code_review_auto_assign_users = ['reviewer1', 'reviewer2', 'reviewer3'];
