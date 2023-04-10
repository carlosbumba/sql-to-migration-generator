<?php

namespace Bumba\Sql2Migration\Reader;

use Bumba\Sql2Migration\Reader\traits\ColumnExtractor;
use Bumba\Sql2Migration\Reader\traits\foreignKeyExtractor;
use Bumba\Sql2Migration\Reader\traits\indexExtractor;
use Bumba\Sql2Migration\Reader\traits\schemaExtractor;
use Bumba\Sql2Migration\Reader\traits\tableExtractor;

/**
 * A class that reads SQL statements from a file and extracts information about the schema and tables.
 */
abstract class SQLReader
{
  use schemaExtractor,
    tableExtractor,
    ColumnExtractor,
    indexExtractor,
    foreignKeyExtractor;

  protected array $tables = [];
  protected array $schemaInfo = [];
  protected string $currentTable = '';


  /**
   * Read and parse an array of SQL lines to extract schema, table, columns, indexes, and foreign keys.
   *
   * @param array $lines An array of SQL lines to parse.
   *
   * @throws \Exception if a primary key references a column that doesn't exist in the current table.
   *
   * @return void
   */
  public function readLines(array $lines)
  {

    foreach ($lines as $i => $line) {
      $line = trim($line);

      if (!strlen($line) || strpos('--', $line) === 0) {
        continue;
      }

      if (stripos($line, 'CREATE SCHEMA') === 0) {
        $this->schemaInfo = $this->extractCurrentSchema($line);
        continue;
      }

      if (stripos($line, 'CREATE TABLE') === 0) {
        $table = $this->extractCurrentTable($line);
        $this->tables[$table]['columns'] = [];
        $this->tables[$table]['indexes'] = [];
        $this->tables[$table]['foreignKeys'] = [];

        $this->currentTable = $table;
        continue;
      }

      if ($this->currentTable) {

        // column definition
        if (str_starts_with($line, '`')) {
          $column = $this->extractCurrentColumn($line);
          $this->tables[$this->currentTable]['columns'][$column['name']] = $column;
        } else {

          // test if a primary key is found
          $primary = $this->extractIfHasPrimaryKeyDeclaration($line);

          if (!empty($primary)) {
            foreach ($primary as $column) {

              if (!array_key_exists($column, $this->tables[$this->currentTable]['columns'])) {
                throw new \Exception("column {$column} not exists in table {$this->currentTable}");
              }

              $this->tables[$this->currentTable]['columns'][$column]['modifiers']['primary'] = true;
            }

            continue;
          }

          // test if is a `alter table add constraint` declaration
          $regex = '@alter table\s\`([a-z0-9\_]+)\`\sadd\sconstraint\s@i';

          if (preg_match($regex, $line)) {
            $table = $this->extractQuotedValues($line)[0];
            $addPos = stripos($line, ' add ');
            $text = substr($line, $addPos + 5);

            $foreignKey = $this->extractSingleLineFKDefinition($text);
            $this->tables[$table]['foreignKeys'][] = $foreignKey;
            continue;
          }

          // test if is a foreign key definition
          $foreignKey = $this->extractIfHasForeignKeyDeclaration($lines, $i);

          if (count($foreignKey)) {
            $this->tables[$this->currentTable]['foreignKeys'][] = $foreignKey;
            continue;
          }

          // test if a index definition
          $index = $this->extractIfHasIndexDeclaration($line);

          if (count($index)) {
            $this->tables[$this->currentTable]['indexes'][] = $index;
          }
        }
      }
    }
  }


  /**
   *@purpose help dbms tests
   */
  public function parseColumn(string $definition)
  {
    return $this->extractCurrentColumn($definition);
  }

  public function getSchema()
  {
    return $this->schemaInfo;
  }
  public function getTables()
  {
    return $this->tables;
  }
  public function getTable(string $name)
  {
    return $this->tables[$name] ?? null;
  }
}