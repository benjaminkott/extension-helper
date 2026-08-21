<?php
declare(strict_types=1);

/*
 * This file is part of the bk2k/extension-helper.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\ExtensionHelper\Command\Release;

use BK2K\ExtensionHelper\Utility\GitUtility;
use BK2K\ExtensionHelper\Utility\VersionUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateCommand extends Command
{
    protected function configure()
    {
        $this->setName('release:create');
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $version = $input->getArgument('version');

        if (!$this->runPreChecks($io, (string)$version)) {
            return Command::FAILURE;
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
            ]
        ];
        foreach ($commands as $command => $arguments) {
            array_unshift($arguments, $command);
            $this->callCommand($command, $arguments, $output);
        }

        return Command::SUCCESS;
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

    private function runPreChecks(SymfonyStyle $io, string $version): bool
    {
        // Check if version argument has the correct format
        if (!VersionUtility::isValid($version)) {
            $io->error(sprintf('No valid version number provided! Example: extension-helper %s 1.0.0', $this->getName()));
            return false;
        }

        // Check if there are untracked files
        $untrackedFiles = GitUtility::getUntrackedFiles();
        if ($untrackedFiles !== []) {
            $io->warning('The following untracked files will be added to your commit.');
            $io->listing($untrackedFiles);
            if (!$io->confirm('Are you sure you want to add these files to your release commit?', false)) {
                $io->writeln(sprintf('Release stopped. Please clean up your working directory and re-run %s', $this->getName()));
                return false;
            }
        }

        // Check if there are uncommitted changes
        $files = GitUtility::getFilesWithUncommitedChanges();
        if ($files !== []) {
            $io->warning('The following files contain uncommitted changes.');
            $io->listing($files);
            if (!$io->confirm('Are you sure you want to add these changes to your release commit?', false)) {
                $io->writeln(sprintf('Release stopped. Please clean up your working directory and re-run %s', $this->getName()));
                return false;
            }
        }

        return true;
    }
}
