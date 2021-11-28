<?php

namespace App\Infrastructure\Command\Account;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{

    protected function configure(): void
    {
        $this
            ->setName('autocrypto:test:test')
            ->setDescription("Test command")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Uso básico de secciones
        $section1 = $output->section();
        $section2 = $output->section();

        $section1->writeln('Hello');
        sleep(1);
        $section2->writeln('World!');
        sleep(1);
        $section1->overwrite('Goodbye');
        sleep(1);
        $section2->clear();


        // Progressbars
        $progress1 = new ProgressBar($section1);
        $progress2 = new ProgressBar($section2);

        $progress1->start(100);
        $progress2->start(100);

        $i = 0;
        while (++$i < 100) {
            $progress1->advance();

            if ($i % 2 === 0) {
                $progress2->advance(4);
            }

            usleep(50000);
        }

        return Command::SUCCESS;
    }
}