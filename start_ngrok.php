<?php
$output = [];
$return_var = 0;
exec('ngrok http 8000 --log=stdout > ngrok_web.log 2>&1 &', $output, $return_var);
echo "Status: " . $return_var . "\n";
print_r($output);
