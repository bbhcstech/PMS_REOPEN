<?php

namespace App\Services;

use InvalidArgumentException;

/** Arithmetic expressions only: no PHP evaluation, properties, strings or executable code. */
class PayrollExpression
{
    private array $tokens = [];
    private int $position = 0;
    private int $depth = 0;
    private const FUNCTIONS = ['MIN', 'MAX', 'ROUND', 'ABS', 'FLOOR', 'CEIL', 'IF'];

    private function parse(string $expression): array
    {
        $expression = strtoupper(rtrim(trim($expression), "; \t\n\r"));
        if ($expression === '' || strlen($expression) > 4000) throw new InvalidArgumentException('Enter an expression of at most 4,000 characters.');
        $offset = 0;
        $this->tokens = [];
        while ($offset < strlen($expression)) {
            if (!preg_match('/\G\s*(\d+(?:\.\d*)?(?:E[+-]?\d+)?|\.\d+|[A-Z_][A-Z0-9_]*|>=|<=|==|!=|&&|\|\||[+\-*\/%()?:,< >!])\s*/A', $expression, $match, 0, $offset)) {
                throw new InvalidArgumentException('Unsupported expression syntax near character '.($offset + 1).'.');
            }
            $this->tokens[] = $match[1];
            $offset += strlen($match[0]);
            if (count($this->tokens) > 500) throw new InvalidArgumentException('Expression is too complex.');
        }
        $this->position = $this->depth = 0;
        $tree = $this->expression();
        if ($this->position !== count($this->tokens)) throw new InvalidArgumentException('Unexpected token: '.$this->peek());
        return $tree;
    }

    private function peek(): ?string { return $this->tokens[$this->position] ?? null; }
    private function take(string $token): void
    {
        if ($this->peek() !== $token) throw new InvalidArgumentException('Expected '.$token.'.');
        $this->position++;
    }

    private function expression(int $minimum = 0): array
    {
        if (++$this->depth > 64) throw new InvalidArgumentException('Expression nesting is too deep.');
        $token = $this->peek();
        $this->position++;
        if (in_array($token, ['+', '-', '!'], true)) $left = ['unary', $token, $this->expression(7)];
        elseif ($token === '(') { $left = $this->expression(); $this->take(')'); }
        elseif ($token !== null && is_numeric($token)) $left = ['number', (float) $token];
        elseif ($token !== null && preg_match('/^[A-Z_][A-Z0-9_]*$/', $token)) {
            if ($this->peek() === '(') {
                if (!in_array($token, self::FUNCTIONS, true)) throw new InvalidArgumentException('Unsupported function '.$token.'.');
                $this->take('('); $arguments = [];
                if ($this->peek() !== ')') {
                    do { $arguments[] = $this->expression(); if ($this->peek() !== ',') break; $this->take(','); } while (true);
                }
                $this->take(')');
                $arity = count($arguments);
                if (($token === 'IF' && $arity !== 3) || (in_array($token, ['ABS', 'FLOOR', 'CEIL'], true) && $arity !== 1)
                    || ($token === 'ROUND' && ($arity < 1 || $arity > 2)) || (in_array($token, ['MIN', 'MAX'], true) && $arity < 2)) {
                    throw new InvalidArgumentException('Incorrect argument count for '.$token.'.');
                }
                $left = ['call', $token, $arguments];
            } else $left = ['variable', $token];
        } else throw new InvalidArgumentException('Expected a number, variable or parenthesized expression.');
        $precedence = ['||' => 1, '&&' => 2, '==' => 3, '!=' => 3, '<' => 4, '>' => 4, '<=' => 4, '>=' => 4, '+' => 5, '-' => 5, '*' => 6, '/' => 6, '%' => 6];
        while (isset($precedence[$this->peek() ?? '']) && $precedence[$this->peek()] >= $minimum) {
            $operator = $this->peek(); $this->position++;
            $left = ['binary', $operator, $left, $this->expression($precedence[$operator] + 1)];
        }
        if ($minimum === 0 && $this->peek() === '?') {
            $this->take('?'); $yes = $this->expression(); $this->take(':');
            $left = ['conditional', $left, $yes, $this->expression()];
        }
        $this->depth--;
        return $left;
    }

    public function variables(string $expression): array
    {
        $tree = $this->parse($expression); $variables = [];
        $walk = function (array $node) use (&$walk, &$variables) {
            if ($node[0] === 'variable') $variables[] = $node[1];
            foreach (array_slice($node, 1) as $child) {
                if (!is_array($child)) continue;
                if (isset($child[0]) && is_array($child[0])) foreach ($child as $argument) $walk($argument);
                elseif (isset($child[0])) $walk($child);
            }
        };
        $walk($tree);
        return array_values(array_unique($variables));
    }

    public function evaluate(string $expression, array $variables): float
    {
        $tree = $this->parse($expression); $bindings = [];
        foreach ($variables as $key => $value) {
            if (!is_numeric($value) || !is_finite((float) $value)) throw new InvalidArgumentException('Variable '.strtoupper((string) $key).' must be a finite number.');
            $bindings[strtoupper((string) $key)] = (float) $value;
        }
        foreach ($this->variables($expression) as $name) if (!array_key_exists($name, $bindings)) throw new InvalidArgumentException('Missing value for '.$name.'.');
        return $this->value($tree, $bindings);
    }

    private function value(array $node, array $variables): float
    {
        $result = match ($node[0]) {
            'number' => $node[1], 'variable' => $variables[$node[1]],
            'unary' => match ($node[1]) { '-' => -$this->value($node[2], $variables), '!' => (float) !$this->value($node[2], $variables), default => $this->value($node[2], $variables) },
            'conditional' => $this->value($node[1], $variables) ? $this->value($node[2], $variables) : $this->value($node[3], $variables),
            'call' => $node[1] === 'IF' ? ($this->value($node[2][0], $variables) ? $this->value($node[2][1], $variables) : $this->value($node[2][2], $variables))
                : $this->functionValue($node[1], array_map(fn ($argument) => $this->value($argument, $variables), $node[2])),
            'binary' => $this->binary($node[1], $node[2], $node[3], $variables),
        };
        if (!is_finite($result) || abs($result) > 1e12) throw new InvalidArgumentException('Result exceeds the supported numeric range.');
        return (float) $result;
    }

    private function functionValue(string $function, array $arguments): float
    {
        return match ($function) {
            'MIN' => min($arguments), 'MAX' => max($arguments), 'ABS' => abs($arguments[0]),
            'FLOOR' => floor($arguments[0]), 'CEIL' => ceil($arguments[0]),
            'ROUND' => round($arguments[0], max(0, min(6, (int) ($arguments[1] ?? 2)))),
        };
    }

    private function binary(string $operator, array $left, array $right, array $variables): float
    {
        $a = $this->value($left, $variables);
        if ($operator === '&&' && !$a) return 0;
        if ($operator === '||' && $a) return 1;
        $b = $this->value($right, $variables);
        if (in_array($operator, ['/', '%'], true) && $b == 0) throw new InvalidArgumentException('Division by zero. Check the denominator inputs.');
        return match ($operator) {
            '+' => $a + $b, '-' => $a - $b, '*' => $a * $b, '/' => $a / $b, '%' => fmod($a, $b),
            '<' => (float) ($a < $b), '>' => (float) ($a > $b), '<=' => (float) ($a <= $b), '>=' => (float) ($a >= $b),
            '==' => (float) ($a == $b), '!=' => (float) ($a != $b), '&&' => (float) ($a && $b), '||' => (float) ($a || $b),
        };
    }
}
