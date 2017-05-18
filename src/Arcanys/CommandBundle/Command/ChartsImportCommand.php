<?php
namespace Arcanys\CommandBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
	Symfony\Component\Finder\Finder;

class ChartsImportCommand extends ContainerAwareCommand
{
	private $filename;
	private $fileLocation = 'app/Resources';
	private $ignoreFirstLine;
	
    protected function configure()
    {
        $this
            ->setName('import:charts')
            ->setDescription('Import the CHARTS csv to the System Table')
			->addArgument('filename', InputArgument::REQUIRED, 'CSV Filename')
            ->addOption('fl', null, InputOption::VALUE_OPTIONAL, 'Ignore First Line?', false)
        ;
    }

    /**
     * Execute the command
     * The environment option is automatically handled.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$conn_main = $this->getContainer()->get('database_connection');
		$error = false;
        $this->filename = $input->getArgument('filename');
		$this->ignoreFirstLine = $input->getOption('fl');
		
		try {
			$csv = $this->parseCSV();
			$output->writeln(sprintf('<info>Processing...</info>'));
			
			foreach($csv as $key => $val) {
				if($this->ignoreFirstLine && $key == 0) { continue; }
				$conn_main->executeUpdate(
					'INSERT INTO
						Chartofaccounts (
							chartname,
							dateadded,
							dateupdated
						)
					VALUES (
						?,
						?,
						?
					)',
					array(
						$val[0],
						date('Y-m-d', strtotime($val[8])),
						date('Y-m-d', strtotime($val[9]))
					)
				);
			}
			$output->writeln(sprintf('<info>Affected rows [Charts of Accounts]: <comment>%s</comment></info>', count($csv)));
		} catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $error = true;
        }
		
		$conn_main->close();

        return $error ? 1 : 0;
    }
	
	/**
     * Parse a csv file
     * 
     * @return array
     */
    private function parseCSV()
    {
        $ignoreFirstLine = $this->ignoreFirstLine;

        $finder = new Finder();
        $finder->files()
            ->in($this->fileLocation)
            ->name($this->filename)
        ;
        foreach ($finder as $file) { $csv = $file; }

        $rows = array();
        if (($handle = fopen($csv->getRealPath(), "r")) !== FALSE) {
            $i = 0;
            while (($data = fgetcsv($handle, null, ",")) !== FALSE) {
                $i++;
                if ($ignoreFirstLine && $i == 1) { continue; }
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $rows;
    }
}