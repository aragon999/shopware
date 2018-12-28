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

namespace Shopware\Bundle\CronBundle\Gateway\Hydrator;

use Shopware\Bundle\CronBundle\Struct\Job;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\Hydrator;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class JobHydrator extends Hydrator
{
    /**
     * @param array $data
     *
     * @return Job
     */
    public function hydrate(array $data)
    {
        $job = new Job();

        $job->setId((int) $data['__cronjob_id']);
        $job->setName($data['__cronjob_name']);
        $job->setAction($this->normalizeAction($data['__cronjob_action']));
        $job->setElementId((int) $data['__cronjob_element_id']);
        $job->setData(unserialize($data['__cronjob_data']));
        $job->setStart($this->getNullOrDatetime($data['__cronjob_start']));
        $job->setInterval($data['__cronjob_interval']);
        $job->setActive((bool) $data['__cronjob_active']);
        $job->setDisableOnError((bool) $data['__cronjob_disable_on_error']);
        $job->setEnd($this->getNullOrDatetime($data['__cronjob_end']));
        $job->setInformTemplate($data['__cronjob_inform_template']);
        $job->setInformMail($data['__cronjob_inform_mail']);
        $job->setPluginId((int) $data['__cronjob_plugin_id']);
        $job->setRunning((bool) $data['__cronjob_running']);

        return $job;
    }

    /**
     * @param string $date
     *
     * @return \DateTime|null
     */
    private function getNullOrDatetime(?string $date): ?\DateTime
    {
        return $date ? new \DateTime($date) : null;
    }

    /**
     * @param string $action
     *
     * @return string
     */
    private function normalizeAction(string $action): string
    {
        if (strpos($action, 'Shopware_') !== 0) {
            $action = str_replace(' ', '', ucwords(str_replace('_', ' ', $action)));
            $action = 'Shopware_CronJob_' . $action;
        }

        return $action;
    }
}
