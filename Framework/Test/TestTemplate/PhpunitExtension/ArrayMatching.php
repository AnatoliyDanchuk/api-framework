<?php

namespace Framework\Test\TestTemplate\PhpunitExtension;

trait ArrayMatching
{
    final protected function assertArray(array $expectation, $actualArray): void
    {
        $this->assertIsArray($actualArray);
        if ($this->arrayContainsCallable($expectation)) {
            foreach ($expectation as $expectedKey => $expectedValue) {
                $this->assertArrayHasKey($expectedKey, $actualArray);
                $actualJsonValue = $actualArray[$expectedKey];
                match (true) {
                    is_scalar($expectedValue) => $this->assertSame($expectedValue, $actualJsonValue),
                    is_callable($expectedValue) => $expectedValue($actualJsonValue),
                    is_array($expectedValue) => $this->assertArray($expectedValue, $actualJsonValue),
                };
            }
        } else {
            $this->assertSameMultiDimensionalArrays($expectation, $actualArray);
        }

        $unexpectedArrayKeys = array_keys(array_diff_key($actualArray, $expectation));
        $this->assertCount(count($expectation), $actualArray,
            'Unexpected keys: ' . implode(', ', $unexpectedArrayKeys)
        );
    }

    private function arrayContainsCallable(array $expectation): bool
    {
        foreach ($expectation as $expectedValue) {
            if (is_array($expectedValue)) {
                $hasCallableExpression = $this->arrayContainsCallable($expectedValue);
                if ($hasCallableExpression) {
                    break;
                }
            }

            if (is_callable($expectedValue)) {
                $hasCallableExpression = true;
                break;
            }
        }

        return $hasCallableExpression ?? false;
    }

    private function assertSameMultiDimensionalArrays(array $expectedArray, array $actualArray): void
    {
        $this->assertSame(json_encode($expectedArray), json_encode($actualArray));
    }
}