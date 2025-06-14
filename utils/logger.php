<?php

// Define the absolute path to the project root
// Assuming 'utils' is directly inside 'your_project_root'
$project_root = dirname(__DIR__); // Go up one level from 'utils' to 'your_project_root'

// Define the log directory
define('LOG_DIR', $project_root . '/logs/');
define('ACTIVITY_LOG_FILE', LOG_DIR . 'activity.log');

/**
 * Logs an activity to the activity.log file.
 *
 * @param string $message The message to log.
 * @param string $level The log level (e.g., 'INFO', 'WARNING', 'ERROR').
 * @param string $username The username associated with the activity, if available.
 */
function log_activity($message, $level = 'INFO', $username = 'SYSTEM') {
    // Ensure the log directory exists
    if (!is_dir(LOG_DIR)) {
        mkdir(LOG_DIR, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $log_entry = sprintf("[%s] %s - User: %s - %s\n", $level, $timestamp, $username, $message);

    // Use file_put_contents with FILE_APPEND to add to the end of the file
    // and LOCK_EX to prevent race conditions during concurrent writes.
    file_put_contents(ACTIVITY_LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

?>