<?php

namespace Smartbox\Integration\FrameworkBundle\Events;


class TimingEvent extends Event
{
    const CONSUMER_TIMING = 'smartesb.consumer.timing';

    /**
     * @var integer
     */
    private $intervalMs;

    /**
     * @var \Smartbox\Integration\FrameworkBundle\Core\Messages\MessageInterface
     */
    protected $message;

    /**
     * @return integer
     */
    public function getIntervalMs()
    {
        return $this->intervalMs;
    }

    /**
     * @param integer $intervalMs
     */
    public function setIntervalMs($intervalMs)
    {
        $this->intervalMs = $intervalMs;
    }

    /**
     * @return \Smartbox\Integration\FrameworkBundle\Core\Messages\MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param \Smartbox\Integration\FrameworkBundle\Core\Messages\MessageInterface $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}