<?php

use Bumba\Sql2Migration\Reader\PostgreSQL\Reader;
use PHPUnit\Framework\TestCase;


class PostgreSQLTest extends TestCase
{
  protected Reader $reader;

  protected function setUp(): void
  {
    $this->reader = new Reader;
  }

  public function test_serial_column_extraction()
  {
    $line = '`id` SERIAL NOT NULL';

    $expected = [
      'name' => 'id', 'str-name'  => "'id'",
      'datatype' => 'serial', 'datatype-arg' => null,
      'modifiers' => [
        'unsigned' => false, 'nullable' => false,
        'default' => null, 'primary' => false,
        'serial' => true
      ]
    ];

    $actual = $this->reader->parseColumn($line);

    $this->assertSame($expected, $actual);
  }
}
