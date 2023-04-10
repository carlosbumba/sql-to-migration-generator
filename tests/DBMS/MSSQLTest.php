<?php

use Bumba\Sql2Migration\Reader\MSSQL\Reader;
use PHPUnit\Framework\TestCase;


class MSSQLTest extends TestCase
{
  protected Reader $reader;

  protected function setUp(): void
  {
    $this->reader = new Reader;
  }

  public function test_simple_identity_column_extraction()
  {
    $line = '`id` int identity(1,1) not null';

    $expected = [
      'name' => 'id', 'str-name'  => "'id'",
      'datatype' => 'integer', 'datatype-arg' => null,
      'modifiers' => [
        'unsigned' => false, 'nullable' => false,
        'default' => null, 'primary' => false,
        'identity' => [1,1]
      ]
    ];

    $actual = $this->reader->parseColumn($line);

    $this->assertSame($expected, $actual);
  }

  public function test_step_two_identity_column_extraction()
  {
    $line = '`id` int identity(1,2) not null';

    $expected = [
      'name' => 'id', 'str-name'  => "'id'",
      'datatype' => 'integer', 'datatype-arg' => null,
      'modifiers' => [
        'unsigned' => false, 'nullable' => false,
        'default' => null, 'primary' => false,
        'identity' => [1,2]
      ]
    ];

    $actual = $this->reader->parseColumn($line);

    $this->assertSame($expected, $actual);
  }
}
