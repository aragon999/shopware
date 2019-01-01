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

namespace Shopware\Bundle\CronBundle\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Shopware\Bundle\CronBundle\Cron\Job\AbstractDatabaseJob;

class JobPersisterGateway implements JobPersisterGatewayInterface
{
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Persists the given job to the database
     *
     * @param AbstractDatabaseJob $job
     */
    public function updateJob(AbstractDatabaseJob $job)
    {
        $jobStruct = $job->getJobStruct();
        if (!$jobStruct->getId()) {
            return;
        }

        $qb = $this->db->createQueryBuilder();
        $qb
            ->update('s_crontab', 'cron')
            ->set('cron.action', ':action')
            ->set('cron.data', ':data')
            ->set('cron.start', ':start')
            ->set('cron.interval', ':interval')
            ->set('cron.active', ':active')
            ->set('cron.running', ':running')
            ->set('cron.end', ':end')
            ->where($qb->expr()->eq('cron.id', ':id'))
            ->setParameters([
                'id' => $jobStruct->getId(),
                'action' => $jobStruct->getAction(),
                'data' => serialize($jobStruct->getData()),
                'interval' => $jobStruct->getInterval(),
                'active' => $jobStruct->isActive(),
                'running' => $jobStruct->isRunning(),
            ])
            ->setParameter(':start', $jobStruct->getStart(), Type::DATETIME)
            ->setParameter(':next', $jobStruct->getNext(), Type::DATETIME)
            ->setParameter(':end', $jobStruct->getEnd(), Type::DATETIME)
        ;
        $qb->execute();
    }
}
