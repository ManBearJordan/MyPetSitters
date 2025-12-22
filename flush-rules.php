<?php
require_once('/var/www/html/wp-load.php');
echo "Flushing Rewrite Rules...\n";
flush_rewrite_rules();
echo "Done.\n";
