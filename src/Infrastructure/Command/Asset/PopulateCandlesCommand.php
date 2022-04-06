<?php

namespace App\Infrastructure\Command\Asset;

use App\Domain\Model\Asset\Pair;
use App\Domain\Repository\Asset\PairRepository;
use App\Domain\Repository\Trading\CandleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;


class PopulateCandlesCommand extends Command
{
    private const REMOTE_URL_TEMPLATE = 'https://data.binance.vision/data/spot/monthly/klines/%pair%/1m/%pair%-1m-%year%-%month%.zip';
    private const DOWNLOAD_URL_TEMPLATE = './data/downloaded/%pair%_%year%_%month%.zip';
    private const EXTRACT_FOLDER_URL_TEMPLATE = './data/extracted/';
    private const EXTRACTED_FILE_URL_TEMPLATE = './data/extracted/%pair%-1m-%year%-%month%.csv';

    private const INSERT_HEADER = "INSERT INTO candle (pair_id, timespan, timestamp, open, high, low, close, volume, trades) VALUES ";


    private PairRepository $pairRepo;
    private CandleRepository $candleRepo;
    private EntityManagerInterface $entityManager;

    private array $query_rows = [];


    public function __construct(PairRepository $pairRepo, CandleRepository $candleRepo, EntityManagerInterface $entityManager)
    {
        $this->pairRepo = $pairRepo;
        $this->candleRepo = $candleRepo;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        // Ex. php bin/console autocrypto:populate:candles ADAEUR,SHIBEUR 2021 11 2022 03
        $this
            ->setName('autocrypto:populate:candles')
            ->setDescription("Downloads a ZIP file with 1m candles, extracts them in a CSV file, creates SQL queries and executes them.")
            ->addArgument('pairs', InputArgument::REQUIRED, 'Comma-separated symbol of the pairs')
            ->addArgument('year_from', InputArgument::REQUIRED, 'format: YYYY')
            ->addArgument('month_from', InputArgument::REQUIRED, 'format: MM')
            ->addArgument('year_to', InputArgument::REQUIRED, 'format: YYYY')
            ->addArgument('month_to', InputArgument::REQUIRED, 'format: MM')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $year_from = $input->getArgument('year_from');
        $month_from = $input->getArgument('month_from');
        $year_to = $input->getArgument('year_to');
        $month_to = $input->getArgument('month_to');
        if (($year_from > $year_to) || ($year_from === $year_to && $month_from > $month_to)) {
            throw new \InvalidArgumentException("Date from ($year_from-$month_from) can't be later than date to ($year_to-$month_to).");
        }

        $dates = $this->completeDates($year_from, $month_from, $year_to, $month_to);
        $pairs = explode(',', $input->getArgument('pairs'));

        foreach ($dates as $date) {
            foreach ($pairs as $pair_symbol) {
                $pair = $this->pairRepo->findBySymbolOrFail($pair_symbol);
                $pair_symbol = $this->mapPairSymbol($pair_symbol);

                $year = $date['year'];
                $month = $date['month'];

                if ($this->checkSkipMonth($pair, $year, $month)) {
                    $output->writeln("Skipped $pair_symbol - $year/$month");
                    continue;
                }

                $remote_url = str_replace(
                    ['%pair%', '%year%', '%month%'],
                    [$pair_symbol, $year, $month],
                    self::REMOTE_URL_TEMPLATE
                );
                $download_url = str_replace(
                    ['%pair%', '%year%', '%month%'],
                    [$pair_symbol, $year, $month],
                    self::DOWNLOAD_URL_TEMPLATE
                );
                $extract_folder_url = str_replace(
                    ['%pair%', '%year%', '%month%'],
                    [$pair_symbol, $year, $month],
                    self::EXTRACT_FOLDER_URL_TEMPLATE
                );
                $extracted_file_url = str_replace(
                    ['%pair%', '%year%', '%month%'],
                    [$pair_symbol, $year, $month],
                    self::EXTRACTED_FILE_URL_TEMPLATE
                );

                $output->writeln("Working on $pair_symbol - $year/$month");
                try {
                    $output->writeln(" ... downloading");
                    $this->downloadFile($remote_url, $download_url);

                    $output->writeln(" ... extracting");
                    $this->extractFile($download_url, $extract_folder_url);

                    $output->writeln(" ... populating DB");
                    $this->prepareAndExecuteQueries($extracted_file_url, $pair);
                }
                catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                    return Command::INVALID;
                }

            }
        }

        return Command::SUCCESS;
    }

    private function completeDates(int $year_from, int $month_from, int $year_to, int $month_to): array
    {
        $dates = [];
        $cur_year = $year_from;
        $cur_month = $month_from;
        while ($cur_year < $year_to || ($cur_year === $year_to && $cur_month <= $month_to)) {
            $dates[] = [
                'year' => (string)$cur_year,
                'month' => str_pad($cur_month, 2, "0", STR_PAD_LEFT),
            ];

            if ($cur_month !== 12) {
                $cur_month++;
            }
            else {
                $cur_year++;
                $cur_month = 1;
            }
        }
        return $dates;
    }

    private function checkSkipMonth(Pair $pair, $year, $month): bool
    {
        $date_from = new \DateTime("$year-$month-01 00:00:00");
        $date_to = new \DateTime($date_from->format("Y-m-t 23:59:00"));

        $count = $this->candleRepo->countForPairInRange($pair, 1, $date_from, $date_to);
        if ($count === 0) {
            return false;
        }

        $max_candles_in_month = (int)$date_to->format('d') * 24 * 60;
        if ($count === $max_candles_in_month) {
            return true;
        }

        $this->candleRepo->deleteForPairInRange($pair, 1, $date_from, $date_to);
        return false;
    }

    private function mapPairSymbol(string $symbol): string
    {
        return match ($symbol) {
            'XDGEUR' => 'DOGEEUR',
            default => $symbol,
        };
    }

    private function downloadFile(string $src_url, string $dest_url): void
    {
        $file = fopen($dest_url, "w");
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $src_url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FILE           => $file,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        fclose($file);

        if (filesize($dest_url) < 1000) {
            throw new \Exception("Fichero vacío");
        }
    }

    private function extractFile(string $src_url, string $dest_folder_url): void
    {
        $zip = new ZipArchive;
        $zip->open($src_url);
        $zip->extractTo($dest_folder_url);
        $zip->close();
    }

    private function prepareAndExecuteQueries(string $src_file_url, Pair $pair): void
    {
        // 1. Open CSV file in read mode
        if (!($file = fopen($src_file_url, 'r'))) {
            throw new \Exception("Invalid file");
        }

        // 2. Read line by line
        $count = 0;
        while ($candle = fgetcsv($file)) {

            $this->query_rows[] = sprintf("(%d, %d, %d, %s, %s, %s, %s, %s, %d)",
                $pair->getId(),
                1,                          // Timespan
                (int)$candle[0]/1000,       // Timestamp    --> Kraken: /1;    Binance: /1000
                $candle[1],                 // Open (str)
                $candle[2],                 // High (str)
                $candle[3],                 // Low (str)
                $candle[4],                 // Close (str)
                $candle[5],                 // Volume (str)
                (int)$candle[8]             // Trades       --> Kraken: [6];    Binance: [8]
            );

            if (++$count % 100 === 0) {
                $query = self::INSERT_HEADER . implode(',', $this->query_rows) . ';';
                $this->entityManager->getConnection()->executeStatement($query);
                $count = 0;
                $this->query_rows = [];
            }
        }

        if ($count > 0) {
            $query = self::INSERT_HEADER . implode(',', $this->query_rows) . ';';
            $this->entityManager->getConnection()->executeStatement($query);
            $this->query_rows = [];
        }

    }

}