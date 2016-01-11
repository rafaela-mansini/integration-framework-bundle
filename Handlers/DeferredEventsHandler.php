<?php

namespace Smartbox\Integration\FrameworkBundle\Handlers;

use JMS\Serializer\Annotation as JMS;
use Smartbox\CoreBundle\Type\Traits\HasType;
use Smartbox\Integration\FrameworkBundle\Messages\EventMessage;
use Smartbox\Integration\FrameworkBundle\Messages\Message;
use Smartbox\Integration\FrameworkBundle\Messages\MessageInterface;
use Smartbox\Integration\FrameworkBundle\Traits\UsesEventDispatcher;
use Smartbox\Integration\FrameworkBundle\Helper\EndpointHelper;

/**
 * Class DeferredEventsHandler
 * @package Smartbox\Integration\FrameworkBundle\Handlers
 */
class DeferredEventsHandler implements HandlerInterface
{
    use HasType;
    use UsesEventDispatcher;

    /**
     * @param MessageInterface $message
     * @return MessageInterface
     */
    public function handle(MessageInterface $message)
    {
        if(!$message instanceof EventMessage){
            throw new \InvalidArgumentException("Expected EventMessage as an argument");
        }

        if($message->getHeader(Message::HEADER_VERSION) != Message::getFlowsVersion()){
            throw new \Exception("Received message with wrong version in deferred events handler. Expected: "
                .Message::getFlowsVersion().", received: "
                .$message->getHeader(Message::HEADER_VERSION)
            );
        }

        $this->eventDispatcher->dispatch($message->getHeader(EventMessage::HEADER_EVENT_NAME).'.deferred', $message->getBody());
    }

}