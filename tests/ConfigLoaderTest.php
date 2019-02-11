<?php declare(strict_types=1);

namespace DiffSniffer\Tests;

use DiffSniffer\ConfigLoader;
use DiffSniffer\Exception;
use PHPUnit\Framework\TestCase;
use VirtualFileSystem\FileSystem;

/**
 * @covers \DiffSniffer\ConfigLoader
 */
class ConfigLoaderTest extends TestCase
{
    /**
     * @var FileSystem
     */
    private $fs;

    /**
     * @var ConfigLoader
     */
    private $loader;

    protected function setUp() : void
    {
        parent::setUp();

        $this->fs = new FileSystem();
        $this->loader = new ConfigLoader();
    }

    /**
     * @test
     * @dataProvider successProvider
     */
    public function success(array $structure, string $dir, array $expected)
    {
        $this->fs->createStructure($structure);
        $dir = $this->fs->path($dir);
        $config = $this->loader->loadConfig($dir);

        if (isset($expected['installed_paths'])) {
            $expected['installed_paths'] = implode(',', array_map(function ($path) {
                return $this->fs->path($path);
            }, explode(',', $expected['installed_paths'])));
        }

        $this->assertSame($expected, $config);
    }

    public static function successProvider() : array
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
                                ],
                            ],
                        ],
                    ],
                ],
                '/project',
                [
                    'foo' => 'bar',
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider notFoundProvider
     */
    public function notFound(array $structure, string $dir)
    {
        $this->fs->createStructure($structure);
        $dir = $this->fs->path($dir);
        $config = $this->loader->loadConfig($dir);

        $this->assertNull($config);
    }

    public static function notFoundProvider() : array
    {
        return [
            'no-composer-config' => [
                [
                    'project' => [
                    ],
                ],
                '/project',
            ],
            'no-phpcs-config' => [
                [
                    'project' => [
                        'composer.json' => '{}',
                    ],
                ],
                '/project',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider failureProvider
     */
    public function failure(array $structure, string $dir)
    {
        $this->fs->createStructure($structure);
        $dir = $this->fs->path($dir);

        $this->expectException(Exception::class);
        $this->loader->loadConfig($dir);
    }

    public static function failureProvider() : array
    {
        return [
            'invalid-composer-config' => [
                [
                    'project' => [
                        'composer.json' => '{',
                    ],
                ],
                '/project',
            ],
        ];
    }
}
