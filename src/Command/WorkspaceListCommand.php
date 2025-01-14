<?php

namespace ADR\Command;

use ADR\Filesystem\AutoDiscoverConfig;
use ADR\Filesystem\Config;
use ADR\Filesystem\Workspace;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Command to list ADRs in workspace
 *
 * @author José Carlos <josecarlos@globtec.com.br>
 */
class WorkspaceListCommand extends Command
{
    private Filesystem $filesystem;

    private AutoDiscoverConfig $autoDiscoverConfig;

    public function __construct(
        string $root
    ) {
        $this->root = $root;
        $this->filesystem = new Filesystem();
        $this->autoDiscoverConfig = new AutoDiscoverConfig($root);
        parent::__construct('workspace:list');
    }

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this
            ->setName('workspace:list')
            ->setDescription('List the ADRs')
            ->setHelp('This command allows you list the ADRs')
            ->addOption(
                'config',
                null,
                InputOption::VALUE_REQUIRED,
                'Config file (default: adr.yml)',
            );
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config(
            $this->autoDiscoverConfig->getConfig($input->getOption('config') ?: null)
        );
        $workspace = new Workspace($config->directory());

        $records = $workspace->records();

        asort($records);

        if (empty($records)) {
            $output->writeln('<info>Workspace is empty</info>');
        } else {
            $style = new SymfonyStyle($input, $output);
            $style->table(
                ['Filename'],
                array_map(function ($record) {
                    return [$record];
                }, $records)
            );
        }

        return 0;
    }
}
