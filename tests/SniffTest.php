<?php

namespace DiffSniffer\Tests;

use DiffSniffer\Changeset;
use DiffSniffer\Runner;
use PHPUnit\Framework\TestCase;

class SniffTest extends TestCase
{
    public function testSniff()
    {
        $_SERVER['argv'] = array();

        /** @var Changeset|\PHPUnit_Framework_MockObject_MockObject $changeSet */
        $changeSet = $this->getMockForAbstractClass(Changeset::class);
        $changeSet->expects($this->once())
            ->method('getDiff')
            ->willReturn(file_get_contents(__DIR__ . '/fixtures/workspace.diff'));

        $changeSet->expects($this->once())
            ->method('getContents')
            ->with('main.php')
            ->willReturn(
                file_get_contents(__DIR__ . '/fixtures/workspace/main.php')
            );

        /** @var Runner|\PHPUnit_Framework_MockObject_MockObject $runner */
        $runner = $this->createPartialMock(Runner::class, array());

        $this->expectOutputString(
            file_get_contents(__DIR__ . '/fixtures/output.txt')
        );

        $this->assertEquals(1, $runner->run($changeSet));
    }
}
