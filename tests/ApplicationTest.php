<?php declare(strict_types=1);

namespace DiffSniffer\Tests;

use function chdir;
use DiffSniffer\Application;
use DiffSniffer\Command;
use Dummy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use const DIRECTORY_SEPARATOR;

class ApplicationTest extends TestCase
{
    /**
     * @test
     * @dataProvider useCaseProvider
     */
    public function testUseCase(string $useCase, int $expectedExitCode)
    {
        $app = new Application();

        $this->expectOutputString($this->getExpectedOutput($useCase));
        $exitCode = $app->run($this->createCommand($useCase), [__FILE__]);

        $this->assertSame($expectedExitCode, $exitCode);
    }

    public function testClassLoader()
    {
        $app = new Application();

        $app->run($this->createCommand('class-loader'), [__FILE__]);

        self::assertTrue(class_exists(Dummy::class));
    }

    private function createCommand(string $useCase) : Command
    {
        $dir = $this->getDirectory($useCase);

        $changeset = new FixtureChangeset($dir);
        chdir($dir . DIRECTORY_SEPARATOR . 'tree');

        /** @var Command|MockObject $command */
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('createChangeset')
            ->willReturn($changeset);

        return $command;
    }

    private function getExpectedOutput(string $useCase) : string
    {
        $dir = $this->getDirectory($useCase);

        $output = file_get_contents($dir . DIRECTORY_SEPARATOR . 'output.txt');
        $this->assertIsString($output);
        $output = str_replace("\n", PHP_EOL, $output);

        return $output;
    }

    private function getDirectory(string $useCase) : string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $useCase;
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
            'exclude-pattern' => [
                'exclude-pattern',
                1,
            ],
            'class-loader' => [
                'class-loader',
                0,
            ],
        ];
    }
}
