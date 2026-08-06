<?php
$content = file_get_contents('routes/gel.php');
// Remove added Mail and Invitations routes that I erroneously placed here
$content = preg_replace('/(\/\/ -- Invitations Secrétaire ------------------------.*?Route::post\(\'\/invitations.*?->name\(\'gel-secretary\.invitations\.reject\'\);)/s', '', $content);
$content = preg_replace('/(\/\/ -- Webmail Externe.*?Route::post\(\'\/mail\/config.*?save-config\'\);)/s', '', $content);
file_put_contents('routes/gel.php', $content);
echo "Cleaned routes/gel.php";
