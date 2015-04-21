<?php

namespace FlorianEc\CsvExcel\Command;

use PHPExcel_IOFactory;
use Plum\Plum\Converter\HeaderConverter;
use Plum\Plum\Converter\NullConverter;
use Plum\Plum\Filter\SkipFirstFilter;
use Plum\Plum\Workflow;
use Plum\Plum\Writer\ArrayWriter;
use Plum\PlumConsole\ConsoleProgressWriter;
use Plum\PlumCsv\CsvReader;
use Plum\PlumCsv\CsvWriter;
use Plum\PlumExcel\ExcelReader;
use Plum\PlumExcel\ExcelWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CsvToExcelCommand
 *
 * @package   FlorianEc\CsvExcel\Command
 * @author    Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright 2015 Florian Eckerstorfer
 * @license   http://opensource.org/licenses/MIT The MIT License
 */
class ConvertCommand extends Command
{
    protected function configure()
    {
        $this->setName('convert')
             ->setDescription('Convert CSV to Excel or Excel to CSV')
             ->addArgument('input', InputArgument::REQUIRED, 'Input file')
             ->addArgument('output', InputArgument::OPTIONAL, 'Output file');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputFile  = $input->getArgument('input');
        $outputFile = $input->getArgument('output');
        $inputFormat  = substr($inputFile, strrpos($inputFile, '.')+1);
        $outputFormat = $outputFile ? substr($outputFile, strrpos($outputFile, '.')+1) : null;

        $output->writeln(sprintf('Convert <info>%s</info> into <info>%s</info>', $inputFormat, $outputFormat));

        $workflow = new Workflow();

        if ($inputFormat === 'csv') {
            $reader = new CsvReader($inputFile);
            $outputFormat = $outputFormat ? $outputFormat : 'xlsx';
            $outputFile   = $outputFile ? $outputFile : str_replace('.csv', '.xlsx', $inputFile);
        } else if ($inputFormat === 'xlsx' || $inputFormat === 'xls') {
            $reader = new ExcelReader(PHPExcel_IOFactory::load($inputFile));
            $outputFormat = $outputFormat ? $outputFormat : 'csv';
            $outputFile   = $outputFile   ? $outputFile : str_replace(['.xlsx', '.xls'], '.csv', $inputFile);
        } else {
            $output->writeln(sprintf('<error>Invalid input file format %s.</error>', $inputFormat));

            return;
        }

        $workflow->addConverter(new HeaderConverter());
        $workflow->addFilter(new SkipFirstFilter(1));

        if ($outputFormat === 'csv') {
            $writer = new CsvWriter($outputFile);
            $writer->autoDetectHeader();
        } else if ($outputFormat === 'xlsx' || $outputFormat == 'xls') {
            $writer = new ExcelWriter($outputFile);
            $writer->autoDetectHeader();
        } else {
            $output->writeln(sprintf('<error>Invalid output file format: %s</error>', $outputFormat));

            return;
        }

        $workflow->addConverter(new NullConverter());
        $workflow->addWriter($writer);
        $workflow->addWriter(new ConsoleProgressWriter(new ProgressBar($output, $reader->count())));

        $result = $workflow->process($reader);

        $output->writeln('');
        $output->writeln(sprintf('Read rows:    %d', $result->getReadCount()));
        $output->writeln(sprintf('Written rows: %d', $result->getItemWriteCount()));
        $output->writeln(sprintf('Error rows:   %d', $result->getErrorCount()));

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            foreach ($result->getExceptions() as $exception) {
                $output->writeln(sprintf('- <error>%s</error>', $exception->getMessage()));
                if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $output->writeln(sprintf('<error>%s</error>', $exception->getTraceAsString()));
                }
            }
        }
    }
}
