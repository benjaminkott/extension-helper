<?php
declare(strict_types=1);

/*
 * This file is part of the bk2k/extension-helper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\ExtensionHelper\Composer\Command\Archive;

use BK2K\ExtensionHelper\Utility\GitUtility;
use BK2K\ExtensionHelper\Utility\VersionUtility;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateCommand extends BaseCommand
{
    protected static $defaultName = 'archive:create';

    protected function configure()
    {
        $this->setName(self::$defaultName);
        $this->setDescription('Create archive for TER-Upload');
        $this->setDefinition(
            new InputDefinition([
                new InputArgument('version', InputArgument::OPTIONAL)
            ])
        );
    }

    /**
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Check if shell exec is available
        if (!function_exists('shell_exec')) {
            $io->error('Please enable shell_exec and rerun this script.');
            exit(1);
        }

        // Check if version argument has the correct format
        $version = $input->getArgument('version');
        if (!VersionUtility::isValid($version)) {
            $io->error('No valid version number provided! Example: extension-helper changelog:create 1.0.0');
            exit(1);
        }

        try {
            $filename = GitUtility::getArchive($version);
        } catch (\InvalidArgumentException $e) {
            $io->error($e->getMessage());
            exit(1);
        }

        $io->success('Archive "' . $filename . '" created');
    }
}
