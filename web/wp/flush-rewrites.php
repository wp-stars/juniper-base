<?php
// Load WordPress core
require_once('wp-load.php');

// Flush rewrite rules
flush_rewrite_rules(true); // Passing true will flush rules and regenerate .htaccess file

echo "Rewrite rules flushed successfully.\n";
