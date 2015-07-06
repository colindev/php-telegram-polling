<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloCommand extends Command
{
    public function configure()
    {
        $this->setName('hello')
            ->setDescription('打招呼')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                '打招呼對象'
            )
            ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                '改為全大寫表示'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        $text = $name ? "Hello, {$name}" : 'Hello';

        if ($input->getOption('yell')) {
            $text = strtoupper($text);
        }

        $output->writeln($text);
    }
}
