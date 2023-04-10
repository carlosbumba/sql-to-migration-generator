<?php

use Bumba\Sql2Migration\Reader\traits\tableExtractor;
use PHPUnit\Framework\TestCase;


class tableExtractorTest extends TestCase
{
  use tableExtractor;

  public function test_table_name_extraction()
  {
    $line = 'CREATE TABLE IF NOT EXISTS `mydb`.`template` (';
    $expected = 'template';

    $this->assertSame($expected, $this->extractCurrentTable($line));
  }
}
