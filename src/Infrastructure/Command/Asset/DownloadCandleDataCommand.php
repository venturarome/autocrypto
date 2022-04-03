<?php

namespace App\Infrastructure\Command\Asset;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;


class DownloadCandleDataCommand extends Command
{
    private const REMOTE_URL_TEMPLATE = 'https://data.binance.vision/data/spot/monthly/klines/%pair%/1m/%pair%-1m-%year%-%month%.zip';
    private const DOWNLOAD_URL_TEMPLATE = './data/downloaded/%pair%_%year%_%month%.zip';
    private const EXTRACT_URL_TEMPLATE = './data/extracted/';


    protected function configure(): void
    {
        // Ex. php bin/console autocrypto:download:candle_data ADAEUR,SHIBEUR 2021 11 2022 03
        $this
            ->setName('autocrypto:download:candle_data')
            ->setDescription("Downloads monthly candle data per minute in zip format and extracts it in a CSV file")
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
            foreach ($pairs as $pair) {
                $year = $date['year'];
                $month = $date['month'];
                $remote_url = str_replace(
                    ['%pair%', '%year%', '%month%'],
                    [$pair, $year, $month],
                    self::REMOTE_URL_TEMPLATE
                );
                $download_url = str_replace(
                    ['%pair%', '%year%', '%month%'],
                    [$pair, $year, $month],
                    self::DOWNLOAD_URL_TEMPLATE
                );
                $extract_url = str_replace(
                    ['%pair%', '%year%', '%month%'],
                    [$pair, $year, $month],
                    self::EXTRACT_URL_TEMPLATE
                );

                $output->writeln("Working on $pair - $year/$month ...");
                $this->downloadFile($remote_url, $download_url);
                $this->extractFile($download_url, $extract_url);
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
    }

    private function extractFile(string $src_url, string $dest_url): void
    {
        $zip = new ZipArchive;
        $zip->open($src_url);
        $zip->extractTo($dest_url);
        $zip->close();
    }

}