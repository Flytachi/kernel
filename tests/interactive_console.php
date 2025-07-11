<?php

echo "PHP Interactive Console\n";
echo "Type 'exit' to quit.\n";

while (true) {
    echo "> ";
    $line = trim(fgets(STDIN));

    if ($line === 'exit') {
        break;
    }
    if ($line === 'url') {
        echo "= hello" . PHP_EOL;
    }
}

echo "Exiting console.\n";
