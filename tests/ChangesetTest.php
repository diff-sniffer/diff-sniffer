<?php declare(strict_types=1);

namespace DiffSniffer\Git\Tests;

use DiffSniffer\Git\Changeset;
use DiffSniffer\Git\Cli;
use PHPUnit\Framework\TestCase;

class ChangesetTest extends TestCase
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var Cli
     */
    private $cli;

    protected function setUp()
    {
        parent::setUp();

        $dir = tempnam(sys_get_temp_dir(), 'diff-sniffer-test');
        unlink($dir);
        mkdir($dir, 0777, true);

        $this->cli = new Cli();
        $this->dir = $dir;

        $this->cli->exec('git init', $this->dir);
        $this->cli->exec('git config user.name phpunit', $this->dir);
        $this->cli->exec('git config user.email phpunit@example.com', $this->dir);
    }

    protected function tearDown()
    {
        $this->cli->exec(
            $this->cli->cmd('rm', '-rf', $this->dir)
        );

        parent::tearDown();
    }

    public function testChangeset()
    {
        file_put_contents($this->dir . '/file1.txt', <<<EOF
Hello, world #1!

EOF
        );
        file_put_contents($this->dir . '/file2.txt', <<<EOF
Hello, world #2!

EOF
        );

        $this->cli->exec('git add .', $this->dir);
        $this->cli->exec('git commit -m"Initial commit" --no-verify', $this->dir);

        file_put_contents($this->dir . '/file1.txt', <<<EOF
Line #2

EOF
            , FILE_APPEND);

        file_put_contents($this->dir . '/file2.txt', <<<EOF
Line #2

EOF
            , FILE_APPEND);

        $this->cli->exec('git add file1.txt', $this->dir);

        $changeset = new Changeset($this->cli, $this->dir);
        $this->assertEquals(<<<EOF
diff --git a/file1.txt b/file1.txt
index 6326b19..2ec3083 100644
--- a/file1.txt
+++ b/file1.txt
@@ -1 +1,2 @@
 Hello, world #1!
+Line #2

EOF
        , $changeset->getDiff());

        $this->assertEquals(<<<EOF
Hello, world #1!
Line #2

EOF
            , $changeset->getContents('file1.txt'));

        $this->assertEquals(<<<EOF
Hello, world #2!

EOF
            , $changeset->getContents('file2.txt'));
    }

    /** @test */
    public function fileWithOnlyDeletedLinesIsIgnored()
    {
        file_put_contents($this->dir . '/file.txt', <<<EOF
Line1
Line2

EOF
        );

        $this->cli->exec('git add .', $this->dir);
        $this->cli->exec('git commit -m"Initial commit" --no-verify', $this->dir);

        file_put_contents($this->dir . '/file.txt', <<<EOF
Line2

EOF
        );
        $this->cli->exec('git add file.txt', $this->dir);

        $changeset = new Changeset($this->cli, $this->dir);
        $this->assertSame('', $changeset->getDiff());
    }
}
