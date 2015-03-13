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
             ->addArgument('output', InputArgument::REQUIRED, 'Output file');
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
        $outputFormat = substr($outputFile, strrpos($outputFile, '.')+1);

        $output->writeln(sprintf('Convert <info>%s</info> into <info>%s</info>', $inputFormat, $outputFormat));

        $workflow = new Workflow();

        if ($inputFormat === 'csv') {
            $reader = new CsvReader($inputFile);
            $workflow->addConverter(new HeaderConverter());
            $workflow->addFilter(new SkipFirstFilter(1));
        } else if ($inputFormat === 'xlsx' || $inputFormat === 'xls') {
            $reader = new ExcelReader(PHPExcel_IOFactory::load($inputFile));
            $reader->setHeaderRow(0);
        } else {
            $output->writeln(sprintf('<error>Invalid input file format %s.</error>', $inputFormat));

            return;
        }

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
        $output->writeln(sprintf('Read items:    %d', $result->getReadCount()));
        $output->writeln(sprintf('Written items: %d', $result->getItemWriteCount()));
        $output->writeln(sprintf('Error items:   %d', $result->getErrorCount()));

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
