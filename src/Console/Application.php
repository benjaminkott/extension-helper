<?php
declare(strict_types=1);

/*
 * This file is part of the bk2k/extension-helper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\ExtensionHelper\Console;

use BK2K\ExtensionHelper\Command;
use Composer\InstalledVersions;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Application
 */
class Application extends BaseApplication
{
    const PACKAGE_NAME = 'bk2k/extension-helper';

    public function __construct()
    {
        parent::__construct('Extension Helper', self::resolveVersion());
        $this->addCommands([
            new Command\Archive\CreateCommand(),
            new Command\Changelog\CreateCommand(),
            new Command\Release\CreateCommand(),
            new Command\Release\PublishCommand(),
            new Command\Version\SetCommand(),
        ]);
    }

    /**
     * The version is resolved from the composer runtime instead of being
     * maintained in this class, so it always matches the installed package.
     */
    private static function resolveVersion(): string
    {
        try {
            return InstalledVersions::getPrettyVersion(self::PACKAGE_NAME) ?? 'UNKNOWN';
        } catch (\OutOfBoundsException $e) {
            return 'UNKNOWN';
        }
    }
}
