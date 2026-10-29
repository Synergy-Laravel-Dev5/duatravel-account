<?php
$output = shell_exec('git status --short');
echo $output;
