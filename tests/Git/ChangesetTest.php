<?php

declare(strict_types=1);

namespace DiffSniffer\Tests\Git;

use DiffSniffer\Cli;
use DiffSniffer\Git\Changeset;
use PHPUnit\Framework\TestCase;
use function defined;
use function escapeshellarg;
use function file_put_contents;
use function mkdir;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class ChangesetTest extends TestCase
{
    /** @var string */
    private static $dir;

    /** @var Cli */
    private static $cli;

    public static function setUpBeforeClass() : void
    {
        $dir = tempnam(sys_get_temp_dir(), 'diff-sniffer-test');
        self::assertIsString($dir);

        unlink($dir);
        mkdir($dir, 0777, true);

        self::$cli = new Cli();
        self::$dir = $dir;

        self::git('init');
        self::git('config', 'user.name', 'phpunit');
        self::git('config', 'user.email', 'phpunit@example.com');
        self::git('config', 'commit.gpgsign', 'false');

        self::putContents('file.txt', 'A');
        self::add('file.txt');
        self::commit('Commit A');

        self::putContents('file.txt', 'B');
        self::add('file.txt');
        self::commit('Commit B');

        self::git('checkout', '-b', 'feature');
        self::putContents('file.txt', 'C-feature');
        self::add('file.txt');
        self::commit('Commit C-feature');

        self::git('checkout', 'master');
        self::putContents('file.txt', 'C');
        self::add('file.txt');
        self::commit('Commit C');

        self::putContents('file.txt', 'C-staged');
        self::add('file.txt');

        self::putContents('file.txt', 'C-working');
    }

    public static function tearDownAfterClass() : void
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $cmd = 'rmdir /S /Q ' . escapeshellarg(self::$dir);
        } else {
            $cmd = self::$cli->cmd('rm', '-rf', self::$dir);
        }

        self::$cli->exec($cmd);
    }

    /**
     * @test
     */
    public function stagedToHead() : void
    {
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
    public function stagedToCommit() : void
    {
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
    public function workingToStaged() : void
    {
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
    public function workingToCommit() : void
    {
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
    public function commitToCommit() : void
    {
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
    public function commitToCommitRangeNotation() : void
    {
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
    public function commitToMergeBase() : void
    {
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
    public function fileWithOnlyDeletedLinesIsIgnored() : void
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

    private static function putContents(string $path, string $contents, int $flags = 0) : void
    {
        file_put_contents(self::$dir . '/' . $path, $contents . "\n", $flags);
    }

    private static function git(string ...$args) : void
    {
        self::$cli->exec(
            self::$cli->cmd('git', ...$args),
            self::$dir
        );
    }

    private static function add(string $path) : void
    {
        self::git('add', $path);
    }

    private static function commit(string $message) : void
    {
        self::git('commit', '-m', $message, '--no-verify');
    }

    private function createChangeset(string ...$args) : Changeset
    {
        return new Changeset(self::$cli, $args, self::$dir);
    }

    private function assertDiff(string $expected, Changeset $changeset) : void
    {
        $this->assertSame($expected . "\n", $changeset->getDiff());
    }

    private function assertContents(string $expected, Changeset $changeset, string $path) : void
    {
        $this->assertSame($expected . "\n", $changeset->getContents($path));
    }
}
