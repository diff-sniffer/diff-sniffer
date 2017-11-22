<?php declare(strict_types=1);

namespace DiffSniffer\Tests;

use DiffSniffer\VersionFormatter;
use PHPUnit\Framework\TestCase;

class VersionFormatterTest extends TestCase
{
    /**
     * @var VersionFormatter
     */
    private $formatter;

    protected function setUp()
    {
        parent::setUp();

        $this->formatter = new VersionFormatter();
    }

    /**
     * @test
     * @dataProvider formatProvider
     */
    public function format(string $version, string $expected)
    {
        $this->assertSame($expected, $this->formatter->format($version));
    }

    public static function formatProvider()
    {
        return [
            'dev-branch' => [
                '3.0.9999999.9999999-dev@e7b847c081239db41380651d8fe262a0df9941be',
                '3.0-dev@e7b847c',
            ],
            'tag-3-numbers' => [
                '3.0.0.0@a51fd0c9a97c54715aba3de2f7fee493f87fd962',
                '3.0.0',
            ],
            'tag-4-numbers' => [
                '3.0.0.1@cc8cbd4fcbe261f4ec495f924e020f3b9922bf10',
                '3.0.0.1',
            ],
        ];
    }
}
