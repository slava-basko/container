<?php

namespace SDI\Export;

use SDI\AbstractContainer;
use SDI\Container;

class Graphviz extends AbstractContainer implements ExportInterface
{
    /**
     * @var array<non-empty-string, array{
     *     type: string,
     *     shared: bool,
     *     tags: array<non-empty-string>,
     *     dependencies: array<non-empty-string>,
     *     factoryParamName: string,
     *     code: mixed,
     *     symlink: string
     * }>
     */
    private $definitionsMap = [];

    /**
     * @var array<non-empty-string>
     */
    private $missed = [];

    /**
     * @var array<non-empty-string, array<string, string>>
     */
    private $options = [
        'graph' => [
            'ratio' => 'compress',
        ],
        'node' => [
            'fontsize' => '11',
            'fontname' => 'Arial',
        ],
        'edge' => [
            'fontsize' => '9',
            'fontname' => 'Arial',
            'color' => 'grey',
            'arrowhead' => 'open',
            'arrowsize' => '0.5',
        ],
        'node.param' => [
            'shape' => 'oval',
            'style' => 'filled',
            'fillcolor' => '#d4d7ff',
        ],
        'node.definition' => [
            'shape' => 'record',
            'style' => 'filled',
            'fillcolor' => '#eeeeee',
        ],
        'node.definition.shared' => [
            'shape' => 'record',
            'style' => 'filled, dashed',
            'fillcolor' => '#eeeeee',
        ],
        'node.missing' => [
            'shape' => 'parallelogram',
            'style' => 'filled, dotted',
            'fillcolor' => '#ffbfbe',
        ],
    ];

    /**
     * @var \SDI\Container
     */
    private $container;

    /**
     * @param \SDI\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function build(): string
    {
        $this->createSchema();

        $nodes = $this->findNodes();
        $missingNodes = $this->findMissingNodes();
        $nodes = \array_merge($nodes, $missingNodes);

        $edges = $this->findEdges();

        return $this->makeStart() . $this->makeNodes($nodes) . $this->makeEdges($edges) . $this->makeEnd();
    }

    /**
     * @throws \ReflectionException
     */
    private function createSchema(): void
    {
        $this->definitionsMap = [];

        $symlinks = array_flip($this->container->symlinks);

        foreach ($this->container->definitions as $definitionKey => $definition) {
            $shared = false;
            if (isset($this->container->sharedServices[$definitionKey])) {
                $shared = true;
            }

            $tags = [];
            if (isset($this->container->tags[$definitionKey])) {
                $tags = $this->container->tags[$definitionKey];
            }

            $symlink = '';
            if (isset($symlinks[$definitionKey])) {
                $symlink = $symlinks[$definitionKey];
            }

            $definitionParams = [
                'shared' => $shared,
                'tags' => $tags,
                'symlink' => $symlink,
                'dependencies' => [],
            ];

            if (\is_callable($definition)) {
                $r = new \ReflectionFunction($definition); // @phpstan-ignore argument.type

                $definitionParams = \array_merge($definitionParams, [
                    'type' => 'service',
                    'factoryParamName' => '',
                    'code' => $this->getDefinitionClosureCode($r),
                ]);

                if (isset($r->getParameters()[0])) {
                    $parameter = $r->getParameters()[0];
                    $definitionParams = \array_merge($definitionParams, [
                        'factoryParamName' => $parameter->getName(),
                    ]);
                }

                $this->definitionsMap[$definitionKey] = $definitionParams;
            } else {
                $this->definitionsMap[$definitionKey] = \array_merge($definitionParams, [
                    'type' => 'parameter',
                    'factoryParamName' => '',
                    'code' => $definition,
                ]);
            }
        }

        foreach ($this->definitionsMap as $definitionKey => $definitionParams) {
            $this->definitionsMap[$definitionKey] = \array_merge($definitionParams, [
                'dependencies' => $this->findDependencies($definitionKey),
            ]);
        }
    }

    /**
     * @param \ReflectionFunction $r
     * @return string
     */
    private function getDefinitionClosureCode(\ReflectionFunction $r)
    {
        $parameter = '';
        $parameterType = '';
        $parameterName = '';

        if (isset($r->getParameters()[0])) {
            $parameter = $r->getParameters()[0];
            $parameterName = $parameter->getName();

            $rt = $parameter->getType();
            if ($rt instanceof \ReflectionNamedType) {
                $parameterType = $rt->getName();

                if ($parameterType !== Container::class) {
                    throw new \RuntimeException('Invalid definition closure parameter type');
                }
            }
        }

        $str = 'function (';
        if ($parameter) {
            $str .= $parameterType . ' ' . '$' . $parameterName;
        }
        $str .= ') {' . PHP_EOL;
        $lines = \file($r->getFileName()); // @phpstan-ignore argument.type
        if ($lines === false) {
            throw new \RuntimeException('Error reading file');
        }
        for ($l = $r->getStartLine(); $l < $r->getEndLine(); $l++) {
            $str .= $lines[$l];
        }

        return $str;
    }

    /**
     * @param non-empty-string $definitionKey
     * @return array<non-empty-string>
     */
    private function findDependencies(string $definitionKey): array
    {
        $definitionParams = $this->definitionsMap[$definitionKey];

        $factoryParamName = $definitionParams['factoryParamName'];
        /** @var string $code */
        $code = $definitionParams['code'];

        // array access search
        preg_match_all("/(?<=\\$$factoryParamName\[)(.+?)(?=\])/", $code, $matches);

        // method search
        preg_match_all("/(?<=\\$$factoryParamName-\>get\()(.+?)(?=\))/", $code, $getMatches);

        // tag search
        preg_match_all("/(?<=\\$$factoryParamName-\>getByTag\()(.+?)(?=\))/", $code, $getByTagMatches);

        $allMatches = $matches[1] + $getMatches[1] + $getByTagMatches[1];

        $result = [];
        foreach ($allMatches as $match) {
            $parts = \explode('::', $match);
            if (isset($parts[1]) && $parts[1] == 'class') {
//                $result[] = $parts[0];
                $class = $parts[0];
                if (\substr($class, 0, 1) === '\\') {
                    $class = \substr($class, 1);
                }
                $result[] = \str_replace('\\', '\\\\', $class);
            } else {
                $result[] = \str_replace(['\'', '"'], '', $match);
            }
        }
        $result = array_filter($result);

        foreach ($result as $index => $item) {
            // possible tag, symlink or nonexistent service/param
            if (!isset($this->definitionsMap[$item])) {
                $dependencies = [];

                foreach ($this->definitionsMap as $id => $definition) {
                    if (\in_array($item, $definition['tags'])) {
                        $dependencies[] = $id;
                    }

                    if ($item == $definition['symlink']) {
                        $dependencies[] = $id;
                    }
                }

                if (empty($dependencies)) {
                    $this->missed[] = $item;
                } else {
                    $result = \array_merge($result, $dependencies);
                    unset($result[$index]);
                }
            }
        }

        return \array_filter(\array_values($result));
    }

    /**
     * @param array<non-empty-string, array{
     *      class: string,
     *      attributes: array<string, string>
     *  }> $nodes
     * @return string
     */
    private function makeNodes(array $nodes): string
    {
        $code = '';
        foreach ($nodes as $id => $node) {
            $code .= \sprintf(
                "  node_%s [label=\"%s\"%s];\n",
                $this->dotize($id),
                $node['class'],
                $this->addAttributes($node['attributes'])
            );
        }

        return $code;
    }

    /**
     * @param array<array{from: non-empty-string, to: non-empty-string}> $edges
     * @return string
     */
    private function makeEdges(array $edges): string
    {
        $code = '';
        foreach ($edges as $edge) {
            $code .= \sprintf(
                "  node_%s -> node_%s [style=\"filled\"];\n",
                $this->dotize($edge['from']),
                $this->dotize($edge['to'])
            );
        }

        return $code;
    }

    /**
     * @return array<array{from: non-empty-string, to: non-empty-string}>
     */
    private function findEdges(): array
    {
        $edges = [];
        foreach ($this->definitionsMap as $definitionKey => $definitionParams) {
            foreach ($definitionParams['dependencies'] as $dependency) {
                $edges[] = [
                    'from' => $definitionKey,
                    'to' => $dependency,
                ];
            }
        }

        return $edges;
    }

    /**
     * @return array<non-empty-string, array{
     *     class: string,
     *     attributes: array<string, string>
     * }>
     */
    private function findNodes(): array
    {
        $nodes = [];

        foreach ($this->definitionsMap as $id => $params) {
            $append = '';
            if (\strlen($params['symlink']) > 0) {
                $append = '\n(' . $params['symlink'] . ')';
            }

            if (\class_exists($id)) {
                $optionsKey = $params['shared'] ? 'node.definition.shared' : 'node.definition';

//                if (\substr($id, 0, 1) === '\\') {
//                    $id = \substr($id, 1);
//                }

                $nodes[$id] = [
                    'class' => \str_replace('\\', '\\\\', $id) . $append,
                    'attributes' => $this->options[$optionsKey],
                ];
            } else {
                $nodes[$id] = [
                    'class' => $id . $append,
                    'attributes' => $this->options['node.param'],
                ];
            }
        }

        return $nodes;
    }

    /**
     * @return array<non-empty-string, array{
     *     class: string,
     *     attributes: array<string, string>
     * }>
     */
    private function findMissingNodes(): array
    {
        $nodes = [];
        foreach ($this->missed as $item) {
            $nodes[$item] = [
                'class' => $item,
                'attributes' => $this->options['node.missing'],
            ];
        }

        return $nodes;
    }

    /**
     * @return string
     */
    private function makeStart(): string
    {
        return \sprintf(
            "digraph sc {\n  %s\n  node [%s];\n  edge [%s];\n\n",
            $this->addOptions($this->options['graph']),
            $this->addOptions($this->options['node']),
            $this->addOptions($this->options['edge'])
        );
    }

    /**
     * @return string
     */
    private function makeEnd(): string
    {
        return "}\n";
    }

    /**
     * @param array<string, string> $attributes
     * @return string
     */
    private function addAttributes(array $attributes): string
    {
        $code = [];
        foreach ($attributes as $k => $v) {
            $code[] = \sprintf('%s="%s"', $k, $v);
        }

        return $code ? ', ' . \implode(', ', $code) : '';
    }

    /**
     * @param array<string, string> $options
     * @return string
     */
    private function addOptions(array $options): string
    {
        $code = [];
        foreach ($options as $k => $v) {
            $code[] = \sprintf('%s="%s"', $k, $v);
        }

        return \implode(' ', $code);
    }

    /**
     * @param string $id
     * @return string
     */
    private function dotize(string $id): string
    {
        return (string)\preg_replace('/\W/i', '_', $id);
    }
}
