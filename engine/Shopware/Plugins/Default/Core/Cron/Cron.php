
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

use Cron\Cron;
use Shopware\Components\CSRFWhitelistAware;

class Shopware_Controllers_Backend_Cron extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    public function init()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
    }

    public function indexAction()
    {
        if (!Shopware()->Plugins()->Core()->Cron()->authorizeCronAction($this->Request())) {
            $this->Response()
                ->clearHeaders()
                ->setHttpResponseCode(403)
                ->appendBody('Forbidden');

            return;
        }

        set_time_limit(0);

        $resolver = $this->get('shopware_cron.cron_shopware_resolver');
        $cron = new Cron();
        $cron->setResolver($this->get('shopware_cron.cron_shopware_resolver'));
        $cron->setExecutor($this->get('shopware_cron.cron_executor'));

        $reports = $cron->run();

        while ($cron->isRunning()) {
        }

        foreach ($reports->getReports() as $jobReport) {
            if ($jobReport->isSuccessful()) {
                printf("Cronjob %s was successful\n", $jobReport->getJob()->getJobStruct()->getName());
            } else {
                printf("Cronjob %s failed\n", $jobReport->getJob()->getJobStruct()->getName());
            }
        }
    }

    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'index',
        ];
    }
}
