<?php
/**
 * @package Newscoop
 * @copyright 2011 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\Tools\Console\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    DateTime;

/**
 * XML export to SMD command
 */
class XMLExportSMDCommand extends Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
            ->setName('XMLExportSMD')
            ->setDescription('Export XML files to SMD.')
            ->setDefinition(array(
                new InputArgument('start', InputArgument::REQUIRED, 'Start time'),
                new InputArgument('end', InputArgument::REQUIRED, 'End time'),
                new InputArgument('creator', InputArgument::OPTIONAL, 'Creator user'),
            ))
            ->setHelp(<<<EOT
Export XML files to SMD.
EOT
        );
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = parse_ini_file(APPLICATION_PATH . '/configs/XMLExportSMD.ini');
        
        $contents = array();
        $attachments = array();
        
        $xmlExportService = $this->getHelper('container')->getService('XMLExport');

        $start = DateTime::createFromFormat('Y-m-d h:i:s', $input->getArgument('start'));
        $end = DateTime::createFromFormat('Y-m-d h:i:s', $input->getArgument('end'));

        if ($start === false || $end === false) {
            print("Invalid date times\n");
            exit;
        }

        $creator = $input->getArgument('creator') ? $input->getArgument('creator') : null;
        $articles = $xmlExportService->getArticles($config, $start, $end, $creator);

        $contents = $xmlExportService->getXML($config['articleType'], $config['attachmentPrefix'], $articles);

        $attachments = $xmlExportService->getAttachments($config['attachmentPrefix'], $articles);
        
        $xmlExportService->createArchive($config['directoryName'], $config['fileName'], $contents, $attachments);
        $xmlExportService->upload($config['directoryName'], $config['fileName'], $config['ftpHost'], $config['ftpUsername'], $config['ftpPassword']);
        $xmlExportService->clean($config['directoryName']);
    }
}
