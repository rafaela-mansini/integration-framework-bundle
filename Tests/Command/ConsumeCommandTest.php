<?php

namespace Smartbox\FrameworkBundle\Tests\Command;

use JMS\Serializer\Exception\RuntimeException;
use Smartbox\Integration\FrameworkBundle\Command\ConsumeCommand;
use Smartbox\Integration\FrameworkBundle\Components\Queues\QueueConsumer;
use Smartbox\Integration\FrameworkBundle\Exceptions\Handler\ClosureExceptionHandler;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ConsumeCommandTest extends KernelTestCase
{
    const NB_MESSAGES = 1;
    const URI = 'queue://main/api';

    protected $mockConsumer;

    public function setMockConsumer($expirationCount)
    {
        self::bootKernel();

        $this->mockConsumer = $this
            ->getMockBuilder(QueueConsumer::class)
            ->setMethods(['consume', 'setExpirationCount'])
            ->getMock();
        $this->mockConsumer
            ->method('setExpirationCount')
            ->with($expirationCount);
        $this->mockConsumer
            ->method('consume')
            ->willReturn(true);

        self::$kernel->getContainer()->set('smartesb.consumers.queue', $this->mockConsumer);
        self::$kernel->getContainer()->set('smartesb.consumers.async_queue', $this->mockConsumer);
        self::$kernel->getContainer()->set('doctrine', $this->createMock(RegistryInterface::class));
    }

    public function testExecuteWithKillAfter()
    {
        $this->setMockConsumer(self::NB_MESSAGES);

        $application = new Application(self::$kernel);
        $application->add(new ConsumeCommand());

        $command = $application->find('smartesb:consumer:start');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'uri' => self::URI, // argument
            '--killAfter' => self::NB_MESSAGES, // option
        ));

        $output = $commandTester->getDisplay();
        $this->assertContains('limited to', $output);
        $this->assertContains('Consumer was gracefully stopped', $output);
    }

    public function testExecuteWithoutKillAfter()
    {
        $this->setMockConsumer(0);

        $application = new Application(self::$kernel);
        $application->add(new ConsumeCommand());

        $command = $application->find('smartesb:consumer:start');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'uri' => self::URI, // argument
        ));

        $output = $commandTester->getDisplay();
        $this->assertNotContains('limited to', $output);
        $this->assertContains('Consumer was gracefully stopped', $output);
    }

    public function testUsesReThrowExceptionHandlerByDefault()
    {
        $this->expectException(RuntimeException::class);

        $this->setMockConsumer(0);
        $this->mockConsumer
            ->method('consume')
            ->will($this->throwException(new RuntimeException()));

        $application = new Application(self::$kernel);
        $application->add(new ConsumeCommand());

        $command = $application->find('smartesb:consumer:start');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'uri' => self::URI, // argument
        ));

    }

    public function testCanSetExceptionHandler()
    {
        $called = false;
        $closure = function ($exception) use (&$called) {
            if ('i am exception' === $exception->getMessage()) {
                $called = true;
            }
        };

        $handler = new ClosureExceptionHandler($closure);
        $handler(new \Exception('i am exception'));

        $this->setMockConsumer(0);
        $this->mockConsumer
            ->method('consume')
            ->will($this->throwException(new RuntimeException('i am exception')));

        $command = new ConsumeCommand();
        $command->setExceptionHandler($handler);

        $application = new Application(self::$kernel);
        $application->add($command);

        $command = $application->find('smartesb:consumer:start');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'uri' => self::URI, // argument
        ));

        $this->assertTrue($called, 'The command did not call the closure handler with the correct message.');
    }

}
