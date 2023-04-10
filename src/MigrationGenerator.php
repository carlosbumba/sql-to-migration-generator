<?php

namespace Bumba\Sql2Migration;

use Bumba\Sql2Migration\Reader\SQLReader;
use Bumba\Sql2Migration\Writer\MigrationWriter;

/**
 * Class MigrationGenerator
 *
 * Class responsible for generating migration files from SQL source files.
 *
 * @package Bumba\Sql2Migration
 * @author carlos bumba <carlosbumbadev16@gmail.com>
 */
class MigrationGenerator
{
  /**
   * @var MigrationWriter $frameworkWriter The framework writer object responsible for writing the generated migration files.
   */
  protected MigrationWriter $frameworkWriter;

  /**
   * @var SQLReader $DbmsReader The SQL reader object responsible for reading the SQL source file.
   */
  protected SQLReader $DbmsReader;

  /**
   * @var string|null $sourceFile The path to the SQL source file.
   */
  protected ?string $sourceFile = null;

  /**
   * @var string|null $outputPath The path to the output directory.
   */
  protected ?string $outputPath = null;

  /**
   * MigrationGenerator constructor.
   *
   * @param string $framework The framework name for which migration files will be generated.
   * @param string $readerType The type of SQL database for which migration files will be generated.
   * @throws \Exception If the framework or SQL database type implementation is not found.
   */
  public function __construct(string $framework, string $readerType = 'MySQL')
  {
    // framework implementation
    $namespace = 'Bumba\Sql2Migration\Writer\Frameworks\\';
    $class = $namespace . (ucfirst($framework)) . '\Writer';

    if (!class_exists($class)) {
      throw new \Exception("{$framework} implementation not found");
    }

    $this->frameworkWriter = new $class;

    // DBMS reader
    $class = "Bumba\Sql2Migration\Reader\\{$readerType}\Reader";

    if (!class_exists($class)) {
      throw new \Exception("{$readerType} implementation not found");
    }

    $this->DbmsReader = new $class;
  }

  /**
   * Generate migration files.
   *
   * @throws \Exception If the source file or output directory is not provided.
   */
  public function generate()
  {
    if (is_null($this->sourceFile)) {
      throw new \Exception("Source file not provided");
    }

    if (is_null($this->outputPath)) {
      throw new \Exception("Output directory not provided");
    }

    // read all lines in source file
    $lines = file($this->sourceFile);
    $this->DbmsReader->readLines($lines);
    // writer files to output dir
    $this->frameworkWriter->setOutputDir($this->outputPath);
    $this->frameworkWriter->write($this->DbmsReader->getTables());
  }

  /**
   * Get the output path.
   *
   * @return string The output path.
   */
  public function getOutputPath(): string
  {
    return $this->outputPath;
  }

  /**
   * Set the output path.
   *
   * @param string $outputPath The path to the output directory.
   * @return self
   * @throws \Exception If the output directory does not exist.
   */
  public function setOutputPath(string $outputPath): self
  {
    if (!is_dir($outputPath)) {
      throw new \Exception("Output directory not exists");
    }

    $this->outputPath = $outputPath;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getSourceFile(): string
  {
    return $this->sourceFile;
  }

  /**
   * @param string $sourceFile 
   * @return self
   */
  public function setSourceFile(string $sourceFile): self
  {
    if (!is_file($sourceFile)) {
      throw new \Exception("Source file not exists");
    }

    $this->sourceFile = $sourceFile;
    return $this;
  }
}
