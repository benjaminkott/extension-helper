<?php
declare(strict_types=1);

/*
 * This file is part of the bk2k/extension-helper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\ExtensionHelper\Console;

use BK2K\ExtensionHelper\Composer\Commands;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Application
 */
class Application extends BaseApplication
{
    const VERSION = '1.1.0-DEV';

    public function __construct()
    {
        parent::__construct('Extension Helper', self::VERSION);
        Commands::registerAtConsole($this);
    }
}
