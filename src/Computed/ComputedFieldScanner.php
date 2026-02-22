<?php

declare(strict_types=1);

namespace IBelieve\LarastanStatamic\Computed;

use IBelieve\LarastanStatamic\Blueprints\FieldDefinition;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

final class ComputedFieldScanner
{
    /** @var list<FieldDefinition>|null */
    private ?array $cachedFields = null;

    /**
     * @param  list<string>  $scanPaths
     */
    public function __construct(
        private readonly array $scanPaths,
    ) {}

    /**
     * @return list<FieldDefinition>
     */
    public function scan(): array
    {
        if ($this->cachedFields !== null) {
            return $this->cachedFields;
        }

        $this->cachedFields = [];

        foreach ($this->findPhpFiles() as $filePath) {
            $this->cachedFields = array_merge($this->cachedFields, $this->scanFile($filePath));
        }

        return $this->cachedFields;
    }

    /**
     * @return list<string>
     */
    private function findPhpFiles(): array
    {
        $files = [];

        foreach ($this->scanPaths as $path) {
            if (is_file($path) && str_ends_with($path, '.php')) {
                $files[] = $path;

                continue;
            }

            if (! is_dir($path)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                /** @var \SplFileInfo $file */
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * @return list<FieldDefinition>
     */
    private function scanFile(string $filePath): array
    {
        $content = file_get_contents($filePath);

        if ($content === false || $content === '') {
            return [];
        }

        if (! str_contains($content, 'computed')) {
            return [];
        }

        $parser = (new ParserFactory)->createForNewestSupportedVersion();
        $stmts = $parser->parse($content);

        if ($stmts === null) {
            return [];
        }

        $nodeFinder = new NodeFinder;

        /** @var list<StaticCall> $calls */
        $calls = $nodeFinder->find($stmts, fn (Node $node): bool => $this->isCollectionComputedCall($node));

        $fields = [];

        foreach ($calls as $call) {
            $fields = array_merge($fields, $this->extractFieldsFromCall($call));
        }

        return $fields;
    }

    private function isCollectionComputedCall(Node $node): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        if (! $node->class instanceof Name) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        if ($node->name->name !== 'computed') {
            return false;
        }

        $className = $node->class->toString();

        return in_array($className, [
            'Collection',
            'Statamic\Facades\Collection',
        ], true);
    }

    /**
     * @return list<FieldDefinition>
     */
    private function extractFieldsFromCall(StaticCall $call): array
    {
        $args = $call->getArgs();

        if (count($args) < 2) {
            return [];
        }

        $fieldArg = $args[1]->value;
        $handles = $this->extractFieldHandles($fieldArg);

        $fields = [];

        foreach ($handles as $handle) {
            $fields[] = new FieldDefinition(
                handle: $handle,
                type: 'computed',
            );
        }

        return $fields;
    }

    /**
     * @return list<string>
     */
    private function extractFieldHandles(Node\Expr $expr): array
    {
        if ($expr instanceof String_) {
            return [$expr->value];
        }

        if ($expr instanceof Array_) {
            $values = [];

            foreach ($expr->items as $item) {
                // Associative array: ['field' => fn()] — key is the handle
                if ($item->key instanceof String_) {
                    $values[] = $item->key->value;
                } elseif ($item->value instanceof String_) {
                    // Simple array: ['field1', 'field2'] — value is the handle
                    $values[] = $item->value->value;
                }
            }

            return $values;
        }

        return [];
    }
}
