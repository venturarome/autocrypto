<?php

namespace App\Infrastructure\Command\Asset;

use App\Domain\Model\Shared\Amount\Amount;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ParseCandleDataCommand extends Command
{
    private array $queries = [];

    public function __construct(
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('autocrypto:parse:candle')
            ->setDescription("Reads a CSV file containing historic Candle data")
            ->addArgument('file_path', InputArgument::REQUIRED, 'File full path')
            ->addArgument('pair_id', InputArgument::REQUIRED, 'Id of the referenced Pair of assets')
            ->addArgument('timespan', InputArgument::REQUIRED, 'Time interval for each Candle')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file_path = $input->getArgument('file_path');
        $pair_id = (int)$input->getArgument('pair_id');
        $timespan = (int)$input->getArgument('timespan');

        $output->writeln([
            'Reading file',
            '============',
            'File path: ' . $file_path
        ]);


        // 1. Open CSV file in read mode
        if (!($file = fopen($file_path, 'r'))) {
            $output->writeln("Invalid file");
            return Command::INVALID;
        }

        // 2. Read line by line
        $count = 0;
        $start = microtime(true);
        while ($candle = fgetcsv($file, 0, ',')) {
            $timestamp = (int)$candle[0]/1000;    // Kraken: /1;    Binance: /1000
            $open = $candle[1];
            $high = $candle[2];
            $low = $candle[3];
            $close = $candle[4];
            $volume = $candle[5];
            $trades = (int)$candle[8];  // Kraken: [6];    Binance: [8]

            $this->addInsertRow(
                $pair_id,
                $timespan,
                $timestamp,
                $open,
                $high,
                $low,
                $close,
                $volume,
                $trades,
            );

            $output->writeln("[OK] " . $timestamp . " " . date('Y-m-d H:i:s', $timestamp));
            if (++$count % 100 === 0) {
                $this->dumpInsertsInFile($file_path, $pair_id, $timespan);
                $end = microtime(true);
                $elapsed = $end - $start;
                $output->writeln("==> Elapsed time in processing 100 elements: $elapsed s");
                $start = $end;
            }
        }

        $this->dumpInsertsInFile($file_path, $pair_id, $timespan);

        return Command::SUCCESS;
    }

    private function getInsertHeader(): string
    {
        return "INSERT INTO candle (pair_id, timespan, timestamp, open, high, low, close, volume, trades) VALUES ";
    }

    private function addInsertRow(
        int $pair_id, int $timespan, int $timestamp,
        string $open_str, string $high_str,
        string $low_str, string $close_str,
        string $volume_str, int $trades,
    ): void
    {
        $format = "(%d, %d, %d, %s, %s, %s, %s, %s, %d)";
        $this->queries[] = sprintf($format,
            $pair_id, $timespan, $timestamp,
            $open_str, $high_str,
            $low_str, $close_str,
            $volume_str, $trades
        );
    }

    private function dumpInsertsInFile(string $source_file_path, int $pair_id, int $timespan): void
    {
        $offset = strrpos($source_file_path, '/');
        $base_path = $offset !== false ? substr($source_file_path, 0, $offset) : '';
        $dest_file_path = $base_path . "/result_{$pair_id}_{$timespan}.sql";

        if (!($file = fopen($dest_file_path, 'a'))) {
            throw new Exception("Failed to open file '$dest_file_path'");
        }

        fwrite($file, $this->getInsertHeader() . PHP_EOL);

        $last_idx = count($this->queries)-1;
        foreach ($this->queries as $idx => $query) {
            if ($idx !== $last_idx) { fwrite($file, $query . ',' . PHP_EOL); }
            else { fwrite($file, $query . ';' . PHP_EOL); }
        }

        unset($this->queries);

        fclose($file);
    }
}