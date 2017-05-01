<?php

require_once __DIR__ . '/../vendor/autoload.php';

// .env file management
$dotEnv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotEnv->load();
$dotEnv->required('GOOGLE_BOOKS_API_KEY')->notEmpty();

/**
 * @param string $className
 * @return bool
 */
function autoLoad($className)
{
    $file = sprintf('%s/%s.php', __DIR__, $className);
    if (file_exists($file)) {
        require_once($file);

        return true;
    }

    return false;
}

spl_autoload_register('autoLoad');