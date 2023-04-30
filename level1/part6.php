<?php

$file_path = "counter.txt";

$counter = (int)file_get_contents($file_path);
echo "Number of site visits: " . $counter;
file_put_contents($file_path, ++$counter);
