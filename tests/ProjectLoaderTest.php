<?php

declare(strict_types=1);

namespace DiffSniffer\Tests;

use DiffSniffer\Exception;
use DiffSniffer\ProjectLoader;
use PHPUnit\Framework\TestCase;
use VirtualFileSystem\FileSystem;
use function array_map;
use function explode;
use function implode;

/**
 * @covers \DiffSniffer\ProjectLoader
 */
class ProjectLoaderTest extends TestCase
{
    /** @var FileSystem */
    private $fs;

    protected function setUp() : void
    {
        parent::setUp();

        $this->fs = new FileSystem();
    }

    /**
     * @param array<string,mixed> $structure
     * @param array<string,mixed> $expected
     *
     * @test
     * @dataProvider successProvider
     */
    public function success(array $structure, string $dir, array $expected) : void
    {
        $this->fs->createStructure($structure);
        $dir = $this->fs->path($dir);

        $loader = new ProjectLoader($dir);
        $config = $loader->getPhpCodeSnifferConfiguration();

        if (isset($expected['installed_paths'])) {
            $expected['installed_paths'] = implode(',', array_map(function ($path) {
                return $this->fs->path($path);
            }, explode(',', $expected['installed_paths'])));
        }

        $this->assertSame($expected, $config);
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function successProvider() : iterable
    {
        return [
            'default' => [
                [
                    'project' => [
                        'composer.json' => '{}',
                        'vendor' => [
                            'squizlabs' => [
                                'php_codesniffer' => [
                                    'CodeSniffer.conf' => <<<'EOF'
<?php

$phpCodeSnifferConfig = [
    'installed_paths' => '../../doctrine/coding-standard/lib/,../../drupal/coder/coder_sniffer/',
];
EOF
                                    ,
                                ],
                            ],
                        ],
                        'sub-directory' => [],
                    ],
                ],
                '/project/sub-directory',
                [
// @codingStandardsIgnoreStart
                    'installed_paths' => '/project/vendor/squizlabs/php_codesniffer/../../doctrine/coding-standard/lib/,/project/vendor/squizlabs/php_codesniffer/../../drupal/coder/coder_sniffer/'
// @codingStandardsIgnoreEnd
                ],
            ],
            'vendor-dir' => [
                [
                    'project' => [
                        'composer.json' => '{"vendor-dir":"libraries"}',
                        'libraries' => [
                            'squizlabs' => [
                                'php_codesniffer' => [
                                    'CodeSniffer.conf' => <<<'EOF'
<?php

$phpCodeSnifferConfig = [
    'foo' => 'bar',
];
EOF
                                    ,
                                ],
                            ],
                        ],
                    ],
                ],
                '/project',
                ['foo' => 'bar'],
            ],
        ];
    }

    /**
     * @param array<string,mixed> $structure
     *
     * @test
     * @dataProvider notFoundProvider
     */
    public function notFound(array $structure, string $dir) : void
    {
        $this->fs->createStructure($structure);
        $dir = $this->fs->path($dir);

        $loader = new ProjectLoader($dir);
        $config = $loader->getPhpCodeSnifferConfiguration();

        $this->assertNull($config);
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function notFoundProvider() : iterable
    {
        return [
            'no-composer-config' => [
                [
                    'project' => [],
                ],
                '/project',
            ],
            'no-phpcs-config' => [
                [
                    'project' => ['composer.json' => '{}'],
                ],
                '/project',
            ],
        ];
    }

    /**
     * @param array<string,mixed> $structure
     *
     * @test
     * @dataProvider failureProvider
     */
    public function failure(array $structure, string $dir) : void
    {
        $this->fs->createStructure($structure);
        $dir = $this->fs->path($dir);

        $loader = new ProjectLoader($dir);

        $this->expectException(Exception::class);
        $loader->getPhpCodeSnifferConfiguration();
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function failureProvider() : iterable
    {
        return [
            'invalid-composer-config' => [
                [
                    'project' => ['composer.json' => '{'],
                ],
                '/project',
            ],
        ];
    }
}
