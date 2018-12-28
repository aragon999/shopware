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

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CronListGateway implements CronListGatewayInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection  $connection
     * @param JobHydrator $jobHydrator
     */
    public function __construct(
        Connection $connection,
        Hydrator\JobHydrator $jobHydrator
    ) {
        $this->connection = $connection;
        $this->jobHydrator = $jobHydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $qb = $this->connection->createQueryBuilder();

        $qb
            ->select($this->getCronjobFields())
            ->from('s_crontab', 'cronjobs')
        ;

        $data = $qb->execute()->fetchAll();
        foreach ($data as $row) {
            $job = $this->jobHydrator->hydrate($row);
            $jobs[$job->getAction()] = $job;
        }

        return $jobs;
    }

    private function getCronjobFields()
    {
        return [
            '`id` AS __cronjob_id',
            '`name` AS __cronjob_name',
            '`action` AS __cronjob_action',
            '`elementID` AS __cronjob_element_id',
            '`data` AS __cronjob_data',
            '`start` AS __cronjob_start',
            '`interval` AS __cronjob_interval',
            '`active` AS __cronjob_active',
            '`disable_on_error` AS __cronjob_disable_on_error',
            '`end` AS __cronjob_end',
            '`inform_template` AS __cronjob_inform_template',
            '`inform_mail` AS __cronjob_inform_mail',
            '`pluginID` AS __cronjob_plugin_id',
            '`running` AS __cronjob_running',
        ];
    }
}
