<?php

namespace Bumba\Sql2Migration\Writer;

use Bumba\Sql2Migration\Writer\traits\ColumnParser;
use Bumba\Sql2Migration\Writer\traits\ForeignKeyParser;
use Bumba\Sql2Migration\Writer\traits\IndexParser;
use Bumba\Sql2Migration\Writer\traits\MigrationCreator;

abstract class MigrationWriter
{
  use ColumnParser, IndexParser, ForeignKeyParser, MigrationCreator;

  protected array $schema;
  protected string $template;
  protected string $filename_template;
  protected string $outputDir;


  /**
   * @return string
   */
  public function getOutputDir(): string
  {
    return $this->outputDir;
  }


  /**
   * @param string $outputDir 
   * @return self
   */
  public function setOutputDir(string $outputDir): self
  {
    if (!is_dir($outputDir)) {
      throw new \Exception("Directory '{$outputDir}' not exists");
    }

    $this->outputDir = $outputDir;
    return $this;
  }


  protected function getMigrationContent(array $table)
  {
    $columns = $this->getStatments($table['columns'], 'parseColumn');

    $foreignKeys = $this->getStatments(
      $table['foreignKeys'],
      'parseForeignKey',
      'foreign keys'
    );

    $indexes = $this->getStatments(
      $table['indexes'],
      'parseIndex',
      'indexes'
    );

    if (strlen($foreignKeys)) $foreignKeys = "\n" . $foreignKeys;
    if (strlen($indexes)) $indexes = "\n" . $indexes;

    $content = $columns . $foreignKeys . $indexes;

    return $content;
  }


  public function write(array $tables)
  {
    foreach ($tables as $tablename => $table) {
      // migration content
      $content = $this->getMigrationContent($table);
      // get template and replace variables
      $template = $this->getTemplateContent();
      $newContent = $this->replaceTemplateVariables($template, $tablename, $content);
      // put content
      $path = $this->createFileForTable($tablename);
      file_put_contents($path, $newContent);
    }
  }
}
