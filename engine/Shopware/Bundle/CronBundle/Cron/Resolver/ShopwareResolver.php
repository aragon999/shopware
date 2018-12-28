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

namespace Shopware\Bundle\CronBundle\Cron\Resolver;

use Cron\Resolver\ArrayResolver;
use Doctrine\Common\Collections\ArrayCollection;
use Enlight_Event_EventManager as EventManager;
use Shopware\Bundle\CronBundle\Cron\Job\DatabaseJob;
use Shopware\Bundle\CronBundle\Service\CronListServiceInterface;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ShopwareResolver extends ArrayResolver
{
    /**
     * @var CronListServiceInterface
     */
    private $cronListService;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var bool
     */
    private $shopwareJobsLoaded;

    /**
     * @var bool
     */
    private $force;

    /**
     * @param CronListServiceInterface $cronListService
     * @param eventManager             $eventManager
     */
    public function __construct(
        CronListServiceInterface $cronListService,
        EventManager $eventManager
    ) {
        $this->cronListService = $cronListService;
        $this->eventManager = $eventManager;

        $this->shopwareJobsLoaded = false;
        $this->force = false;

        parent::__construct([]);
    }

    public function setForce(bool $force)
    {
        $this->force = $force;
    }

    /**
     * Return all due jobs
     *
     * @return Job[]
     */
    public function resolve()
    {
        $jobs = $this->jobs;
        if (!$this->shopwareJobsLoaded) {
            $jobs = array_merge($jobs, $this->cronListService->getList());

            $this->shopwareJobsLoaded = true;
        }

        $jobCollection = new ArrayCollection($jobs);
        $this->eventManager->collect(
            'Shopware_Cron_Collect_Plugin_Jobs',
            $jobCollection,
            [
                'force' => $this->force,
            ]
        );

        if ($this->force) {
            $this->jobs = array_map(function ($job) {
                if ($job instanceof DatabaseJob) {
                    $job->setForce(true);
                }

                return $job;
            }, $jobCollection->toArray());
        } else {
            $this->jobs = $jobCollection->toArray();
        }

        if ($this->force) {
            return $this->jobs;
        }

        return parent::resolve();
    }
}
