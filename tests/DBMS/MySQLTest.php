<?php

use Bumba\Sql2Migration\Reader\MySQL\Reader;
use PHPUnit\Framework\TestCase;


class MySQLTest extends TestCase
{
  protected Reader $reader;

  protected function setUp(): void
  {
    $this->reader = new Reader;
  }

  public function test_auto_increment_extraction()
  {
    $line = '`id` INT NOT NULL AUTO_INCREMENT';

    $expected = [
      'name' => 'id', 'str-name'  => "'id'",
      'datatype' => 'integer', 'datatype-arg' => null,
      'modifiers' => [
        'unsigned' => false, 'nullable' => false,
        'default' => null, 'primary' => false,
        'auto_increment' => true
      ]
    ];

    $actual = $this->reader->parseColumn($line);

    $this->assertSame($expected, $actual);
  }
}