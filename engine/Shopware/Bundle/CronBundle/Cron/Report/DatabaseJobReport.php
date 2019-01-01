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

namespace Shopware\Bundle\CronBundle\Cron\Report;

use Cron\Report\JobReport;
use Shopware\Bundle\CronBundle\Cron\Job\AbstractDatabaseJob;
use Shopware\Bundle\CronBundle\Gateway\JobPersisterGatewayInterface;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class DatabaseJobReport extends JobReport
{
    /**
     * @var AbstractDatabaseJob
     */
    protected $job;

    /**
     * @var JobPersisterGatewayInterface
     */
    protected $jobPersister;

    /**
     * @param AbstractDatabaseJob          $job
     * @param JobPersisterGatewayInterface $jobPersister
     */
    public function __construct(AbstractDatabaseJob $job, JobPersisterGatewayInterface $jobPersister)
    {
        $this->job = $job;
        $this->jobPersister = $jobPersister;
    }

    /**
     * {@inheritdoc}
     */
    public function addError($line)
    {
        parent::addError($line);

        $this->job->getJobStruct()->setData(
            $this->getFormatedData()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addOutput($line)
    {
        parent::addError($line);

        $this->job->getJobStruct()->setData(
            $this->getFormatedData()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setEndTime($endTime)
    {
        parent::setEndTime($endTime);

        $this->job->getJobStruct()->setEnd(\DateTime::createFromFormat('U.u', $endTime));
        $this->job->getJobStruct()->setRunning(false);
        $this->jobPersister->updateJob($this->job);
    }

    /**
     * {@inheritdoc}
     */
    public function setStartTime($startTime)
    {
        parent::setStartTime($startTime);

        $this->job->getJobStruct()->setStart(\DateTime::createFromFormat('U.u', $startTime));
        $this->job->getJobStruct()->setRunning(true);
        $this->jobPersister->updateJob($this->job);
    }

    private function getFormatedData(): string
    {
        return implode("\n", $this->getOutput()) . "\n" . implode("\n", $this->getError());
    }
}
