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

use Enlight_Controller_Request_Request as Request;
use Shopware_Components_Config as Config;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CronAuthService implements CronAuthServiceInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function authorizeCronAction(Request $request): bool
    {
        // If called using CLI, always execute the cron tasks
        if (PHP_SAPI === 'cli') {
            return true;
        }

        // At least one of the security policies is enabled.
        // If at least one of them validates, cron tasks will be executed
        $cronSecureAllowedKey = $this->config->get('cronSecureAllowedKey');
        $cronSecureAllowedIp = $this->config->get('cronSecureAllowedIp');
        $cronSecureByAccount = $this->config->get('cronSecureByAccount');

        // No security policy specified, accept all requests
        if (empty($cronSecureAllowedKey) && empty($cronSecureAllowedIp) && !$cronSecureByAccount) {
            return true;
        }

        // Validate key
        if (!empty($cronSecureAllowedKey)) {
            $urlKey = $request->getParam('key');

            if (strcmp($cronSecureAllowedKey, $urlKey) == 0) {
                return true;
            }
        }

        // Validate ip
        if (!empty($cronSecureAllowedIp)) {
            $requestIp = $request->getServer('REMOTE_ADDR');

            if (in_array($requestIp, explode(';', $cronSecureAllowedIp))) {
                return true;
            }
        }

        // Validate user auth
        if ($cronSecureByAccount) {
            if (Shopware()->Container()->get('Auth')->hasIdentity() === true) {
                return true;
            }
        }

        return false;
    }
}
