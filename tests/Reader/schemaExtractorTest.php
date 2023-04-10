<?php

use Bumba\Sql2Migration\Reader\traits\schemaExtractor;
use PHPUnit\Framework\TestCase;


class schemaExtractorTest extends TestCase
{
  use schemaExtractor;

  public function test_schema_informatin()
  {
    $line = 'CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;';
    $expected = [
      'schema' => 'mydb',
      'charset' => 'utf8'
    ];

    $this->assertSameSize(
      $expected,
      $this->extractCurrentSchema($line)
    );

    $line = 'CREATE SCHEMA IF NOT EXISTS `mydb`;';
    $expected = [
      'schema' => 'mydb',
      'charset' => ''
    ];

    $this->assertSameSize(
      $expected,
      $this->extractCurrentSchema($line)
    );
  }
}
