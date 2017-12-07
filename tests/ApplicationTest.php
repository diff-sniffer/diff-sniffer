<?php declare(strict_types=1);

namespace DiffSniffer\Tests;

use function chdir;
use DiffSniffer\Application;
use DiffSniffer\Command;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    /**
     * @test
     * @dataProvider useCaseProvider
     */
    public function testUseCase(string $useCase, int $expectedExitCode)
    {
        $dir = __DIR__ . '/fixtures/' . $useCase;
        $changeset = new FixtureChangeset($dir);
        chdir($dir . '/tree');

        /** @var Command|\PHPUnit_Framework_MockObject_MockObject $command */
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('createChangeset')
            ->willReturn($changeset);
        $app = new Application();

        $expectedOutput = file_get_contents($dir . '/output.txt');
        $expectedOutput = str_replace("\n", PHP_EOL, $expectedOutput);

        $this->expectOutputString($expectedOutput);
        $exitCode = $app->run($command, [__FILE__]);

        $this->assertSame($expectedExitCode, $exitCode);
    }

    public static function useCaseProvider()
    {
        return [
            'only-changed-lines-reported' => [
                'only-changed-lines-reported',
                1,
            ],
            'excluded-rule-cache' => [
                'excluded-rule-cache',
                0,
            ],
        ];
    }
}
