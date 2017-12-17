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

        $this->git('init');
        $this->git('config', 'user.name', 'phpunit');
        $this->git('config', 'user.email', 'phpunit@example.com');
        $this->git('config', 'commit.gpgsign', 'false');
    }

    private function initRepo()
    {
        $this->putContents('file.txt', 'A');
        $this->add('file.txt');
        $this->commit('Commit A');

        $this->putContents('file.txt', 'B');
        $this->add('file.txt');
        $this->commit('Commit B');

        $this->git('checkout', '-b', 'feature');
        $this->putContents('file.txt', 'C-feature');
        $this->add('file.txt');
        $this->commit('Commit C-feature');

        $this->git('checkout', 'master');
        $this->putContents('file.txt', 'C');
        $this->add('file.txt');
        $this->commit('Commit C');

        $this->putContents('file.txt', 'C-staged');
        $this->add('file.txt');

        $this->putContents('file.txt', 'C-working');
    }

    protected function tearDown()
    {
        $this->cli->exec(
            $this->cli->cmd('rm', '-rf', $this->dir)
        );

        parent::tearDown();
    }

    /**
     * @test
     */
    public function stagedToHead()
    {
        $this->initRepo();
        $changeset = $this->createChangeset('--staged');
        $this->assertDiff(<<<EOF
diff --git a/file.txt b/file.txt
index 3cc58df..2ec44f9 100644
--- a/file.txt
+++ b/file.txt
@@ -1 +1 @@
-C
+C-staged
EOF
        , $changeset);

        $this->assertContents(<<<EOF
C-staged
EOF
            , $changeset, 'file.txt');
    }

    /**
     * @test
     */
    public function stagedToCommit()
    {
        $this->initRepo();
        $changeset = $this->createChangeset('--staged', 'HEAD~');
        $this->assertDiff(<<<EOF
diff --git a/file.txt b/file.txt
index 223b783..2ec44f9 100644
--- a/file.txt
+++ b/file.txt
@@ -1 +1 @@
-B
+C-staged
EOF
        , $changeset);

        $this->assertContents(<<<EOF
C-staged
EOF
            , $changeset, 'file.txt');
    }


    /**
     * @test
     */
    public function workingToStaged()
    {
        $this->initRepo();
        $changeset = $this->createChangeset();
        $this->assertDiff(<<<EOF
diff --git a/file.txt b/file.txt
index 2ec44f9..ced1ed9 100644
--- a/file.txt
+++ b/file.txt
@@ -1 +1 @@
-C-staged
+C-working
EOF
        , $changeset);

        $this->assertContents(<<<EOF
C-working
EOF
            , $changeset, 'file.txt');
    }

    /**
     * @test
     */
    public function workingToCommit()
    {
        $this->initRepo();
        $changeset = $this->createChangeset('HEAD~2');
        $this->assertDiff(<<<EOF
diff --git a/file.txt b/file.txt
index f70f10e..ced1ed9 100644
--- a/file.txt
+++ b/file.txt
@@ -1 +1 @@
-A
+C-working
EOF
        , $changeset);

        $this->assertContents(<<<EOF
C-working
EOF
            , $changeset, 'file.txt');
    }

    /**
     * @test
     */
    public function commitToCommit()
    {
        $this->initRepo();
        $changeset = $this->createChangeset('HEAD~2', 'HEAD~1');
        $this->assertDiff(<<<EOF
diff --git a/file.txt b/file.txt
index f70f10e..223b783 100644
--- a/file.txt
+++ b/file.txt
@@ -1 +1 @@
-A
+B
EOF
        , $changeset);

        $this->assertContents(<<<EOF
B
EOF
            , $changeset, 'file.txt');
    }

    /**
     * @test
     */
    public function commitToCommitRangeNotation()
    {
        $this->initRepo();
        $changeset = $this->createChangeset('HEAD~2..HEAD~1');
        $this->assertDiff(<<<EOF
diff --git a/file.txt b/file.txt
index f70f10e..223b783 100644
--- a/file.txt
+++ b/file.txt
@@ -1 +1 @@
-A
+B
EOF
        , $changeset);

        $this->assertContents(<<<EOF
B
EOF
            , $changeset, 'file.txt');
    }

    /**
     * @test
     */
    public function commitToMergeBase()
    {
        $this->initRepo();
        $changeset = $this->createChangeset('HEAD...feature');
        $this->assertDiff(<<<EOF
diff --git a/file.txt b/file.txt
index 223b783..20e8d1f 100644
--- a/file.txt
+++ b/file.txt
@@ -1 +1 @@
-B
+C-feature
EOF
        , $changeset);

        $this->assertContents(<<<EOF
C-feature
EOF
            , $changeset, 'file.txt');
    }

    /**
     * @test
     */
    public function fileWithOnlyDeletedLinesIsIgnored()
    {
        $this->putContents('file.txt', <<<EOF
Line1
Line2
EOF
        );

        $this->add('file.txt');
        $this->commit('Initial commit');

        $this->putContents('file.txt', <<<EOF
Line2
EOF
        );
        $this->add('file.txt');

        $changeset = $this->createChangeset('--staged');
        $this->assertSame('', $changeset->getDiff());
    }

    private function putContents(string $path, string $contents, int $flags = 0) : void
    {
        file_put_contents($this->dir . '/' . $path, $contents . "\n", $flags);
    }

    private function git(string ...$args) : void
    {
        $this->cli->exec(
            $this->cli->cmd('git', ...$args),
            $this->dir
        );
    }

    private function add(string $path) : void
    {
        $this->git('add', $path);
    }

    private function commit(string $message) : void
    {
        $this->git('commit', '-m', $message, '--no-verify');
    }

    private function createChangeset(string ...$args) : Changeset
    {
        return new Changeset($this->cli, $args, $this->dir);
    }

    private function assertDiff(string $expected, Changeset $changeset)
    {
        $this->assertSame($expected . "\n", $changeset->getDiff());
    }

    private function assertContents(string $expected, Changeset $changeset, string $path)
    {
        $this->assertSame($expected . "\n", $changeset->getContents($path));
    }
}
