<?php

namespace Phpactor\Indexer\Adapter\Tolerant\Indexer;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\Indexer\Adapter\Tolerant\TolerantIndexer;
use Phpactor\Indexer\Model\Index;
use SplFileInfo;

class MemberIndexer implements TolerantIndexer
{
    public function canIndex(Node $node): bool
    {
        return $node instanceof ScopedPropertyAccessExpression;
    }

    public function index(Index $index, SplFileInfo $info, Node $node): void
    {
    }

    public function beforeParse(Index $index, SplFileInfo $info): void
    {
    }
}