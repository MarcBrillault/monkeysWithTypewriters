<?php

require_once __DIR__ . '/lib/loader.php';

for ($i = 0; $i < getenv('LOOP_LENGTH'); $i++) {
    $monkey = new Monkey();
    // $monkey->setDev(true);
    $monkey->useTypeWriter();

    sleep(getenv('SLEEP_TIME'));
}

