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

    protected function setUp()
    {
        parent::setUp();

        $this->cli = new Cli();
    }

    public function testEscaping()
    {
        $value = 'Hello, world "\'$!';
        $output = $this->cli->exec(
            $this->cli->cmd('/bin/echo', '-n', $value)
        );

        $this->assertEquals($value, $output);
    }

    public function testFailure()
    {
        $this->expectException(RuntimeException::class);

        $this->cli->exec(
            $this->cli->cmd('false')
        );
    }

    public function testPipe()
    {
        $output = $this->cli->exec(
            $this->cli->pipe(
                $this->cli->cmd('/bin/echo', '-n', 'Hello, world!'),
                $this->cli->cmd('cat')
            )
        );

        $this->assertEquals('Hello, world!', $output);
    }
}
