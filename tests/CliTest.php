<?php declare(strict_types=1);

namespace DiffSniffer\Git\Tests;

use DiffSniffer\Exception\RuntimeException;
use DiffSniffer\Git\Cli;
use PHPUnit\Framework\TestCase;

class CliTest extends TestCase
{
    /**
     * @var Cli
     */
    private $cli;

    protected function setUp() : void
    {
        $this->cli = new Cli();
    }

    public function testEscaping()
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->markTestSkipped('escapeshellarg() doesn\' t work as expected on Windows');
        }

        $value = 'Hello, world "\'$!';
        $output = $this->cli->exec(
            $this->cli->cmd('echo', $value)
        );

        $this->assertEquals($value . PHP_EOL, $output);
    }

    public function testFailure()
    {
        $this->expectException(RuntimeException::class);

        $this->cli->exec(
            $this->cli->cmd('false')
        );
    }

    public function testExecPiped()
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->markTestSkipped('TODO: rework using something else than `cat\'');
        }

        $output = $this->cli->execPiped([
            $this->cli->cmd('echo', 'Hello, world!'),
            $this->cli->cmd('cat'),
        ]);

        $this->assertEquals('Hello, world!' . PHP_EOL, $output);
    }

    /**
     * @test
     */
    public function execPipedFailure()
    {
        $this->expectException(RuntimeException::class);

        $this->cli->execPiped([
            $this->cli->cmd('false'),
            $this->cli->cmd('cat'),
        ]);
    }
}
