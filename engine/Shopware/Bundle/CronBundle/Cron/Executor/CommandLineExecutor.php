<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\CronBundle\Cron\Executor;

use Cron\Executor\Executor as CronExecutor;
use Cron\Executor\ExecutorSet;
use Cron\Report\CronReport;
use Shopware\Bundle\CronBundle\Cron\Job\AbstractDatabaseJob;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CommandLineExecutor extends CronExecutor
{
    /**
     * @var OutputInterface
     */
    protected $consoleOutput;

    /**
     * @param OutputInterface $consoleOutput
     */
    public function setConsoleOutput(OutputInterface $consoleOutput)
    {
        $this->consoleOutput = $consoleOutput;
    }

    /**
     * {@inheritdoc}
     */
    protected function startProcesses(CronReport $report)
    {
        foreach ($this->sets as $set) {
            $jobName = $this->getJobName($set);

            $this->writeConsoleOutput(sprintf('Starting "%s"', $jobName));

            // Execute Job
            $report->addJobReport($set->getReport());
            $set->run();

            $this->writeConsoleOutput(sprintf('Finished "%s"', $jobName));
        }
    }

    /**
     * @param ExecutorSet $set
     *
     * @return string
     */
    protected function getJobName(ExecutorSet $set): string
    {
        $job = $set->getJob();
        if ($job instanceof AbstractDatabaseJob) {
            $jobStruct = $job->getJobStruct();

            return "{$jobStruct->getName()} ({$jobStruct->getAction()})";
        }

        throw new \InvalidArgumentException(sprintf('Could not determine a qualified name of the given Job (%s)', get_class($job)));
    }

    /**
     * Write the given line to the console output if present
     */
    protected function writeConsoleOutput(string $line)
    {
        if (!empty($line) && $this->consoleOutput instanceof OutputInterface) {
            $this->consoleOutput->writeln($line);
        }
    }
}
