<?php
namespace Arcanys\CommandBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand,
    Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
	Symfony\Component\Finder\Finder;

class VendorsImportCommand extends ContainerAwareCommand
{
	private $filename;
	private $fileLocation = 'app/Resources';
	private $ignoreFirstLine;
	
    protected function configure()
    {
        $this
            ->setName('import:vendors')
            ->setDescription('Import the VENDORS csv to the System Table')
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
						Vendor (
							name,
							address,
							contact_person,
							phone_num,
							email,
							dateadded,
							dateupdated,
							city,
							state,
							zip
						)
					VALUES (
						?,
						?,
						?,
						?,
						?,
						?,
						?,
						?,
						?,
						?
					)',
					array(
						$val[1],
						$val[8] . ' ' . $val[9] . ' ' . $val[10] . ' ' . $val[11],
						$val[3],
						$val[18] != '' ? $val[18] : $val[21],
						$val[16] != '' ? $val[16] : $val[20],
						date('Y-m-d', strtotime($val[23])),
						date('Y-m-d', strtotime($val[24])),
						$val[12],
						$val[13],
						$val[14]
					)
				);
			}
			$output->writeln(sprintf('<info>Affected rows [Vendors]: <comment>%s</comment></info>', count($csv)));
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