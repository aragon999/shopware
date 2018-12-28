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

namespace Shopware\Bundle\CronBundle\Service;

use Enlight_Event_EventManager as EventManager;
use Shopware\Bundle\CronBundle\Cron\Job\DatabaseJob;
use Shopware\Bundle\CronBundle\Gateway\CronListGatewayInterface;
use Shopware\Bundle\CronBundle\Gateway\JobPersisterGatewayInterface;
use Shopware\Bundle\CronBundle\Struct\Job;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CronListService implements CronListServiceInterface
{
    /**
     * @var CronListGatewayInterface
     */
    private $cronListGateway;

    /**
     * @var JobPersisterGatewayInterface
     */
    private $jobPersister;

    /**
     * @param CronListGatewayInterface     $cronListGateway
     * @param JobPersisterGatewayInterface $jobPersister
     */
    public function __construct(
        CronListGatewayInterface $cronListGateway,
        JobPersisterGatewayInterface $jobPersister,
        EventManager $eventManager
    ) {
        $this->cronListGateway = $cronListGateway;
        $this->jobPersister = $jobPersister;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(bool $force = false)
    {
        $jobPersister = $this->jobPersister;
        $eventManager = $this->eventManager;

        return array_map(function (Job $job) use ($jobPersister, $eventManager, $force) {
            return new DatabaseJob($job, $jobPersister, $eventManager);
        }, $this->cronListGateway->getList());
    }
}
