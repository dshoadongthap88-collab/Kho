<?php
$content = file_get_contents('database/backup_final.sql');
$content = preg_replace('/\bjson\b/', 'longtext', $content);
file_put_contents('database/backup_final_nojson.sql', $content);
echo "Done";
