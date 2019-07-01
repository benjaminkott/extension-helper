<?php
declare(strict_types=1);

/*
 * This file is part of the bk2k/extension-helper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\ExtensionHelper\Composer;

use BK2K\ExtensionHelper\Command;
use Composer\Plugin\Capability\CommandProvider;

/**
 * Commands
 */
class Commands implements CommandProvider
{
    public function getCommands()
    {
        return array(
            new Command\Archive\CreateCommand,
            new Command\Changelog\CreateCommand,
            new Command\Release\CreateCommand,
            new Command\Release\PublishCommand,
            new Command\Version\SetCommand,
        );
    }
}
