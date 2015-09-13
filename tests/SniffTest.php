<?php

namespace DiffSniffer\Tests;

use DiffSniffer\Changeset;
use DiffSniffer\Runner;

class SniffTest extends \PHPUnit_Framework_TestCase
{
    public function testSniff()
    {
        /** @var Changeset|\PHPUnit_Framework_MockObject_MockObject $changeSet */
        $changeSet = $this->getMockForAbstractClass(Changeset::class);
        $changeSet->expects($this->once())
            ->method('export');
        $changeSet->expects($this->once())
            ->method('getDiff')
            ->willReturn(file_get_contents(__DIR__ . '/fixtures/workspace.diff'));

        /** @var Runner|\PHPUnit_Framework_MockObject_MockObject $runner */
        $runner = $this->getMock(Runner::class, array(
            'createTempDir',
            'scheduleDirectoryRemoval',
            'scheduleFileRemoval',
        ));
        $runner->expects($this->once())
            ->method('createTempDir')
            ->willReturn(__DIR__ . '/fixtures/workspace');

        $this->expectOutputRegex('/FOUND 1 ERROR AFFECTING 1 LINE/');

        $exitCode = $runner->run($changeSet);
        $this->assertEquals(1, $exitCode);
    }
}
