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

use Shopware\Components\CSRFWhitelistAware;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Cron extends \Enlight_Controller_Action implements CSRFWhitelistAware
{
    public function init()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
    }

    public function indexAction()
    {
        $cronAuthService = $this->get('shopware_cron.cron_auth_service');
        if (!$cronAuthService->authorizeCronAction($this->Request())) {
            $response = $this->Response();
            $response
                ->clearHeaders()
                ->setHttpResponseCode(403)
                ->appendBody('Forbidden')
            ;

            return;
        }

        set_time_limit(0);
        $cron = new \Cron\Cron();
        $cron->setResolver($this->get('shopware_cron.cron_shopware_resolver'));
        $cron->setExecutor($this->get('shopware_cron.cron_executor'));

        $cronReport = $cron->run();

        while ($cron->isRunning()) {
        }

        foreach ($cronReport->getReports() as $jobReport) {
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
