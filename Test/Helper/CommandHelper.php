<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application as ConsoleApplication;
use IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase;

/**
 * Command helper class.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class CommandHelper extends AbstractHelper
{
    /**
     * @var \Symfony\Component\Console\Application
     */
    private $console;

    /**
     * @var string
     */
    private $commandName;

    /**
     * @var integer
     */
    private $maxMemory = 5242880; // 5 * 1024 * 1024 KB

    /**
     * {@inheritdoc}
     */
    public function __construct(WebTestCase $testCase)
    {
        parent::__construct($testCase);

        $this->console = new ConsoleApplication($testCase->getClient()->getKernel());
        $this->console->setAutoExit(false);
    }

    /**
     * Define the command name.
     *
     * @param string $commandName
     */
    public function setCommandName($commandName)
    {
        $this->commandName = $commandName;
    }

    /**
     * Retrieve the command input.
     *
     * @param array     $arguments
     *
     * @return \Symfony\Component\Console\Input\InputInterface
     */
    public function getInput(array $arguments = array())
    {
        array_unshift($arguments, $this->commandName);

        $input = new ArrayInput($arguments);
        $input->setInteractive(false);

        return $input;
    }

    /**
     * Define the maximum memory for console command output.
     *
     * @param integer $maxMemory
     */
    public function setMaxMemory($maxMemory)
    {
        $this->maxMemory = $maxMemory;
    }

    /**
     * Execute a console command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface|null $input
     *
     * @return string
     */
    public function run(InputInterface $input = null)
    {
        $handler = fopen('php://temp/maxmemory:' . $this->maxMemory, 'r+');
        $output  = new StreamOutput($handler);

        $this->console->run($input, $output);

        rewind($handler);

        return stream_get_contents($handler);
    }
}
