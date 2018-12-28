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

namespace Shopware\Bundle\CronBundle\Struct;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Job implements \JsonSerializable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var int
     */
    protected $elementId;

    /**
     * @var string
     */
    protected $data;

    /**
     * @var \DateTime
     */
    protected $next;

    /**
     * @var \DateTime
     */
    protected $start;

    /**
     * @var int
     */
    protected $interval;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var bool
     */
    protected $disableOnError;

    /**
     * @var \DateTime
     */
    protected $end;

    /**
     * @var string
     */
    protected $informTemplate;

    /**
     * @var string
     */
    protected $informMail;

    /**
     * @var int
     */
    protected $pluginId;

    /**
     * @var bool
     */
    protected $running;

    /**
     * @return int
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action)
    {
        $this->action = $action;
    }

    /**
     * @return int
     */
    public function getElementId(): int
    {
        return $this->elementId;
    }

    /**
     * @param int $elementId
     */
    public function setElementId(int $elementId)
    {
        $this->elementId = $elementId;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data)
    {
        $this->data = $data;
    }

    /**
     * @return \DateTime
     */
    public function getNext(): ?\DateTime
    {
        if ($this->next) {
            return $next;
        }

        if (!$this->start instanceof \DateTime) {
            return null;
        }

        $next = clone $this->getStart();
        $next->add(\DateInterval::createFromDateString("{$this->getInterval()} seconds"));

        return $next;
    }

    /**
     * @deprecated
     *
     * @param \DateTime $next
     */
    public function setNext(?\DateTime $next)
    {
        $this->next = $next;
    }

    /**
     * @return \DateTime
     */
    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     */
    public function setStart(?\DateTime $start)
    {
        $this->start = $start;
    }

    /**
     * @return int
     */
    public function getInterval(): int
    {
        return $this->interval;
    }

    /**
     * @param int $interval
     */
    public function setInterval(int $interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function shouldDisableOnError(): bool
    {
        return $this->disableOnError;
    }

    /**
     * @param bool $disableOnError
     */
    public function setDisableOnError(bool $disableOnError)
    {
        $this->disableOnError = $disableOnError;
    }

    /**
     * @return \DateTime
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     */
    public function setEnd(?\DateTime $end)
    {
        $this->end = $end;
    }

    /**
     * @return string
     */
    public function getInformTemplate(): string
    {
        return $this->informTemplate;
    }

    /**
     * @param string
     */
    public function setInformTemplate(string $informTemplate)
    {
        $this->informTemplate = $informTemplate;
    }

    /**
     * @return string
     */
    public function getInformMail(): string
    {
        return $this->informMail;
    }

    /**
     * @param string $informMail
     */
    public function setInformMail(string $informMail)
    {
        $this->informMail = $informMail;
    }

    /**
     * @return int
     */
    public function getPluginId(): int
    {
        return $this->pluginId;
    }

    /**
     * @param int $pluginId
     */
    public function setPluginId(int $pluginId)
    {
        $this->pluginId = $pluginId;
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * @param bool $running
     */
    public function setRunning(bool $running)
    {
        $this->running = $running;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
