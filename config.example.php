<?php
/**
 * Local database overrides. Copy this file to config.local.php (gitignored)
 * and replace the placeholders. config.php loads that file when it exists.
 *
 * A stock XAMPP MariaDB install uses user "root" and an empty password.
 * Leave config.local.php absent in that case; config.php already defaults to it.
 */
return [
    'host' => 'localhost',
    'dbname' => 'chatroom_db',
    'username' => 'your_db_user',
    'password' => 'your_db_password',
];
