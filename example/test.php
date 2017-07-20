<?php

# Call TestApi Url
$url = 'http://localhost/php-lib/php-api/example/test';
$html = file_get_contents($url);
error_log($html);
