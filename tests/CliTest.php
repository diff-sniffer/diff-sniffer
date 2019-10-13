<?php

declare(strict_types=1);

namespace DiffSniffer\Tests;

use DiffSniffer\Cli;
use DiffSniffer\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use const PHP_EOL;
use function defined;

class CliTest extends TestCase
{
    /** @var Cli */
    private $cli;

    protected function setUp() : void
    {
        $this->cli = new Cli();
    }

    public function testEscaping() : void
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->markTestSkipped('escapeshellarg() doesn\' t work as expected on Windows');
        }

        $value  = 'Hello, world "\'$!';
        $output = $this->cli->exec(
            $this->cli->cmd('echo', $value)
        );

        $this->assertEquals($value . PHP_EOL, $output);
    }

    public function testFailure() : void
    {
        $this->expectException(RuntimeException::class);

        $this->cli->exec(
            $this->cli->cmd('false')
        );
    }

    public function testExecPiped() : void
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
    public function execPipedFailure() : void
    {
        $this->expectException(RuntimeException::class);

        $this->cli->execPiped([
            $this->cli->cmd('false'),
            $this->cli->cmd('cat'),
        ]);
    }
}
