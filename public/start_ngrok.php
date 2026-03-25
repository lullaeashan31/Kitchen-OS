<?php
$output = [];
$return_var = 0;
// We use a full path to ngrok if possible, or just 'ngrok'
exec('ngrok http 8000 --log=stdout > ../ngrok_web.log 2>&1 &', $output, $return_var);
echo "Attempted to start ngrok. Status: " . $return_var . "<br>";
echo "Check ngrok_web.log in root for details.";
