<?php

namespace Bumba\Sql2Migration;

use Bumba\Sql2Migration\Reader\SQLReader;
use Bumba\Sql2Migration\Writer\MigrationWriter;


/**
 * Undocumented class
 * 
 * 
 * @author carlos bumba <carlosbumbadev16@gmail.com>
 */
class MigrationGenerator
{
  protected MigrationWriter $frameworkWriter;
  protected SQLReader $DbmsReader;
  protected ?string $sourceFile = null;
  protected ?string $outputPath = null;

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
   * @return string
   */
  public function getOutputPath(): string
  {
    return $this->outputPath;
  }

  /**
   * @param string $outputPath 
   * @return self
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
