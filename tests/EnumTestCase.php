<?php

namespace Tests;

abstract class EnumTestCase extends TestCase
{
    public $enum;

    protected function setUp(): void
    {
        parent::setUp();

        $model = $this->getEnumName();
        $this->enum = new $model;
    }

    abstract public function getDescriptiveValues(): array;

    abstract protected function getEnumName(): string;

    public function test_descriptive_values(): void
    {
        $values = $this->enum->getDescriptiveValues();
        $this->assertIsArray($values);
        $except = $this->getDescriptiveValues();
        $this->assertJsonStringEqualsJsonString(collect($except), collect($values));
    }
}
