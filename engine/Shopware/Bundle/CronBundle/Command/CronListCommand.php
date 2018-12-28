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

namespace Shopware\Bundle\CronBundle\Command;

use Shopware\Bundle\CronBundle\Service\CronListServiceInterface;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CronListCommand extends ShopwareCommand
{
    /**
     * @var CronListServiceInterface
     */
    private $cronListManager;

    /**
     * @param CronListServiceInterface $cronListManager
     */
    public function __construct(
        CronListServiceInterface $cronListManager
    ) {
        $this->cronListManager = $cronListManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Lists cronjobs.')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> lists cronjobs.
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container->load('table');

        $cronjobs = $this->cronListManager->getList();

        $rows = [];
        foreach ($cronjobs as $job) {
            $jobStruct = $job->getJobStruct();
            $rows[] = [
                $jobStruct->getName(),
                $jobStruct->getAction(),
                $jobStruct->isActive() ? 'Yes' : 'No',
                $jobStruct->getInterval(),
                ($jobStruct->getNext()) ? $jobStruct->getNext()->format('d.m.Y H:i:s') : '',
                ($jobStruct->getEnd()) ? $jobStruct->getEnd()->format('d.m.Y H:i:s') : '',
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['Name', 'Action', 'Active', 'Interval', 'Next run', 'Last run'])
              ->setRows($rows);

        $table->render();
    }
}
