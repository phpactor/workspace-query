<?php

namespace Phpactor\Indexer\Tests\Integration\ReferenceFinder;

use Generator;
use Phpactor\Indexer\Adapter\ReferenceFinder\IndexedImplementationFinder;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class IndexedImplementationFinderTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    /**
     * @dataProvider provideClassLikes
     * @dataProvider provideClassMembers
     */
    public function testFinder(string $manifest, int $expectedLocationCount): void
    {
        $this->workspace()->loadManifest($manifest);
        [ $source, $offset ] = ExtractOffset::fromSource($this->workspace()->getContents('project/subject.php'));
        $this->workspace()->put('project/subject.php', $source);

        $index = $this->createInMemoryIndex();
        $indexBuilder = $this->createTestBuilder($index);
        $fileList = $this->fileListProvider();
        $indexer = new Indexer($indexBuilder, $index, $fileList);
        $indexer->getJob()->run();

        $implementationFinder = new IndexedImplementationFinder(
            $index,
            $this->createReflector()
        );

        $locations = $implementationFinder->findImplementations(
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt((int)$offset)
        );

        self::assertCount($expectedLocationCount, $locations);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideClassLikes(): Generator
    {
        yield 'interface implementations' => [
            <<<'EOT'
// File: project/subject.php
<?php interface Fo<>oInterface {}
// File: project/class.php
<?php

class Foobar implements FooInterface {}
class Barfoo implements FooInterface {}
EOT
        ,
            2
        ];

        yield 'class implementations' => [
            <<<'EOT'
// File: project/subject.php
<?php class Fo<>o {}
// File: project/class.php
<?php

class Foobar extends Foo {}
class Barfoo extends Foo {}
EOT
        ,
            2
        ];

        yield 'abstract class implementations' => [
            <<<'EOT'
// File: project/subject.php
<?php abstract class Fo<>o {}
// File: project/class.php
<?php

class Foobar extends Foo {}
class Barfoo extends Foo {}
EOT
        ,
            2
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideClassMembers(): Generator
    {
        yield 'class member' => [
            <<<'EOT'
// File: project/subject.php
<?php interface FooInterface {
   public function doT<>his();
}
// File: project/class.php
<?php

class Foobar implements FooInterface {
    public function doThis();
}
EOT
        ,
            1
        ];
    }
}
