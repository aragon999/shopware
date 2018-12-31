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

namespace Shopware\Bundle\CronBundle\Cron\Job;

use Cron\Job\AbstractJob;
use Cron\Report\JobReport;
use Enlight_Event_EventManager as EventManager;
use Shopware\Bundle\CronBundle\Cron\Report\DatabaseJobReport;
use Shopware\Bundle\CronBundle\Cron\Schedule\IntervalSchedule;
use Shopware\Bundle\CronBundle\Gateway\JobPersisterGatewayInterface;
use Shopware\Bundle\CronBundle\Struct;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class DatabaseJob extends AbstractJob
{
    /**
     * @var Struct\Job
     */
    private $jobStruct;

    /**
     * @var JobPersisterGatewayInterface
     */
    private $jobPersister;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var bool
     */
    private $force = false;

    /**
     * @param Struct\Job                   $jobStruct
     * @param JobPersisterGatewayInterface $jobPersister
     * @param EventManager                 $eventManager
     */
    public function __construct(
        Struct\Job $jobStruct,
        JobPersisterGatewayInterface $jobPersister,
        EventManager $eventManager
    ) {
        $this->jobStruct = $jobStruct;
        $this->jobPersister = $jobPersister;
        $this->eventManager = $eventManager;
        $this->schedule = new IntervalSchedule($jobStruct->getStart(), $jobStruct->getInterval());
    }

    /**
     * @param bool $force
     */
    public function setForce(bool $force)
    {
        $this->force = $force;
    }

    /**
     * @return Struct\Job
     */
    public function getJobStruct(): Struct\Job
    {
        return $this->jobStruct;
    }

    /**
     * Validate the job.
     *
     * @param \DateTime $now
     *
     * @return bool
     */
    public function valid(\DateTime $now): bool
    {
        return $this->force || parent::valid($now) && !$this->isRunning();
    }

    public function run(JobReport $report)
    {
        $report->setStartTime(microtime(true));

        $jobStruct = $report->getJob()->getJobStruct();
        try {
            $jobArgs = new \Shopware_Components_Cron_CronJob([
                'subject' => $this,
                'job' => $report->getJob(),
            ]);
            $jobArgs->setReturn($jobStruct->getData());
            $jobArgs = $this->eventManager->notifyUntil(
                $jobStruct->getAction(),
                $jobArgs
            );

            if ($jobArgs !== null) {
                $report->addOutput($jobArgs->getReturn());
            }

            //$report->addOutput($job->getData());
            $report->setEndTime(microtime(true));
            $report->setSuccessful(true);

            $this->eventManager->notify('Shopware_CronJob_Finished_' . $jobStruct->getAction(), [
                'subject' => $this,
                'job' => $report->getJob(),
            ]);
        } catch (\Throwable $e) {
            $report->setSuccessful(false);
            $jobStruct->setEnd(new \DateTime());

            if ($jobStruct->shouldDisableOnError()) {
                $jobStruct->setActive(false);
            }

            $this->eventManager->notify('Shopware_CronJob_Error_' . $jobStruct->getAction(), [
                'subject' => $this,
                'job' => $report->getJob(),
            ]);
        }

        $this->jobPersister->updateJob($report->getJob());
    }

    /**
     * {@inheritdoc}
     */
    public function isRunning(): bool
    {
        return ($this->jobStruct) ? $this->jobStruct->isRunning() : false;
    }

    /**
     * {@inheritdoc}
     */
    public function createReport(): JobReport
    {
        return new DatabaseJobReport($this, $this->jobPersister);
    }
}
