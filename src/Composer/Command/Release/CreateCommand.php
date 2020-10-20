<?php
declare(strict_types=1);

/*
 * This file is part of the bk2k/extension-helper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\ExtensionHelper\Composer\Command\Release;

use BK2K\ExtensionHelper\Utility\VersionUtility;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateCommand extends BaseCommand
{
    protected static $defaultName = 'release:create';

    protected function configure()
    {
        $this->setName(self::$defaultName);
        $this->setDescription('Create Release');
        $this->setDefinition(
            new InputDefinition([
                new InputArgument('version', InputArgument::REQUIRED)
            ])
        );
    }

    /**
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Check if version argument has the correct format
        $version = $input->getArgument('version');
        if (!VersionUtility::isValid($version)) {
            $io->error('No valid version number provided! Example: extension-helper release:create 1.0.0');
            exit(1);
        }

        // Commands to run in sequence
        $commands = [
            'version:set' => [
                'version' => $version
            ],
            'changelog:create' => [
                'version' => $version
            ],
            'release:publish' => [
                'version' => $version
            ],
            'archive:create' => [
                'version' => $version
            ]
        ];
        foreach ($commands as $command => $arguments) {
            array_unshift($arguments, $command);
            $this->callCommand($command, $arguments, $output);
        }
    }

    /**
     * @var string $name
     * @var string $arguments
     * @var OutputInterface $output
     * @return int The command exit code
     */
    protected function callCommand(string $name, array $arguments, OutputInterface $output): int
    {
        $command = $this->getApplication()->find($name);
        $input = new ArrayInput($arguments);
        return $command->run($input, $output);
    }
}
