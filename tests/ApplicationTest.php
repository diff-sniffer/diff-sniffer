<?php

declare(strict_types=1);

namespace DiffSniffer\Tests;

use DiffSniffer\Application;
use DiffSniffer\Command;
use Dummy;
use PHP_CodeSniffer\Autoload;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_shift;
use function chdir;
use function class_exists;
use function file_get_contents;
use function spl_autoload_functions;
use function str_replace;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

class ApplicationTest extends TestCase
{
    /**
     * @test
     * @dataProvider useCaseProvider
     */
    public function testUseCase(string $useCase, int $expectedExitCode): void
    {
        $app = new Application();

        $this->expectOutputString($this->getExpectedOutput($useCase));
        $exitCode = $app->run($this->createCommand($useCase), [__FILE__]);

        $this->assertSame($expectedExitCode, $exitCode);
    }

    public function testClassLoader(): void
    {
        $app = new Application();

        $app->run($this->createCommand('class-loader'), [__FILE__]);

        self::assertTrue(class_exists(Dummy::class));

        $loaders = spl_autoload_functions();
        self::assertIsArray($loaders);
        self::assertSame([Autoload::class, 'load'], array_shift($loaders));
    }

    private function createCommand(string $useCase): Command
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

    private function getExpectedOutput(string $useCase): string
    {
        $dir = $this->getDirectory($useCase);

        $output = file_get_contents($dir . DIRECTORY_SEPARATOR . 'output.txt');
        $this->assertIsString($output);
        $output = str_replace("\n", PHP_EOL, $output);

        return $output;
    }

    private function getDirectory(string $useCase): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $useCase;
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function useCaseProvider(): iterable
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
