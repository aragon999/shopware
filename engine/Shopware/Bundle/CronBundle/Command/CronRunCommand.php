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

use Cron\Cron;
use Cron\Executor\ExecutorInterface;
use Cron\Resolver\ResolverInterface;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CronRunCommand extends ShopwareCommand
{
    /**
     * @var ExecutorInterface
     */
    private $cronExecutor;

    /**
     * @var ResolverInterface
     */
    private $cronResolver;

    /**
     * @param ExecutorInterface $cronExecutor
     * @param ResolverInterface $cronListManager
     */
    public function __construct(
        ExecutorInterface $cronExecutor,
        ResolverInterface $cronResolver
    ) {
        $this->cronExecutor = $cronExecutor;
        $this->cronResolver = $cronResolver;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Runs cronjobs.')
            ->setHelp(
                <<<'EOF'
The <info>%command.name%</info> runs due cronjobs.
EOF
            )
            ->addArgument(
                'cronjob',
                InputArgument::OPTIONAL,
                "If given, only run the cronjob which action matches, e.g. 'Shopware_CronJob_ClearHttpCache'"
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'If given, the cronjob(s) will be run regardless of scheduling'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->registerErrorHandler($output);

        $this->container->load('plugins');

        $jobAction = $input->getArgument('cronjob');
        $force = $input->getOption('force');

        // Prepare Cron resolver
        if ($jobAction) {
            $resolver = clone $this->container->get('shopware_cron.cron_single_job_shopware_resolver');
            $resolver->setJobAction($jobAction);
        } else {
            $resolver = clone $this->container->get('shopware_cron.cron_shopware_resolver');
        }
        $resolver->setForce((bool) $force);

        // Prepare Executor with correct output
        $executor = clone $this->cronExecutor;
        $executor->setConsoleOutput($output);

        $cron = new Cron();
        $cron->setResolver($resolver);
        $cron->setExecutor($executor);

        $time = microtime(true);

        $cron->run();
        while ($cron->isRunning()) {
        }

        $output->writeln('Successfully executed all crons in: ' . (microtime(true) - $time) . ' seconds');

        return 0;
    }
}
