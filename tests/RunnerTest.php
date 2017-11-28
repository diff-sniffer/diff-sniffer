<?php declare(strict_types=1);

namespace DiffSniffer\Tests;

use DiffSniffer\Changeset;
use DiffSniffer\Runner;
use PHP_CodeSniffer\Config;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    public function testEarlyReturn()
    {
        $config = $this->createMock(Config::class);
        $runner = new Runner($config);

        $changeSet = $this->createMock(Changeset::class);
        $changeSet->expects($this->once())
            ->method('getDiff')
            ->willReturn('');

        $this->assertSame(0, $runner->run($changeSet));
    }
}
