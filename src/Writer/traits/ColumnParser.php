<?php

namespace Bumba\Sql2Migration\Writer\traits;


trait ColumnParser
{
  use SignatureParams, SchemaConvention;

 
  protected function parseColumn(array $column)
  {
    $fn = $this->getSchemaFunctionForReplacement($column['datatype']);
    $args = $this->getColumnArgsForReplacement($column);

    $column['modifiers-signatures'] = $this->getModifiersFunction($column['modifiers']);

    // if there is a conventional way, get it
    $this->tryGetSchemaConvention($column, $args);

    // if there is not
    if (!isset($column['signature'])) {
      $column['signature'] =  $this->replaceParams($args, $fn);
    }

    return $this->appendModifersToFinalSignature($column['modifiers-signatures'], $column['signature']);
  }


  protected function getSchemaFunctionForReplacement(string $datatype)
  {
    $fn = $this->schema['datatypes'][$datatype] ?? null;

    if (is_null($fn)) {
      throw new \Exception("DataType {$datatype} don't have a function in this schema");
    }

    return $fn;
  }


  protected function getColumnArgsForReplacement(array $column)
  {
    $args = [];

    // insert datatype arguments
    if (!empty($column['datatype-arg'])) {

      if (in_array($column['datatype'], ['enum', 'set'])) {
        $items = array_map(fn ($i) => is_int($i) ? "'{$i}'" : $i, $column['datatype-arg']);
        $args[] = '[' . implode(', ', $items) . ']';
      } else {
        $args = is_array($column['datatype-arg']) ? [...$column['datatype-arg']] : [$column['datatype-arg']];
      }
    }

    // insert column name at beginning of array
    array_unshift($args, $column['str-name']);

    return $args;
  }


  protected function tryGetSchemaConvention(array &$column, array $args)
  {
    if (!isset($this->schema['conventions']));

    $convention = $this->getConventionByModifier($column) ?? $this->getConventionByDataType($column);

    if ($convention) {
      $column['signature'] = $this->replaceParams($args, $convention);
    }
  }


  protected function getModifiersFunction(array $columnModifiers)
  {
    $result = [];

    foreach ($columnModifiers as $modifer => $value) {
      if (isset($this->schema['modifiers'][$modifer]) and $value) {
        $fn = $this->schema['modifiers'][$modifer];

        if ($value !== true) {

          $fn = $this->replaceParams([$value], $fn);
        }

        $result[$modifer] = $fn;
      }
    }

    return $result;
  }
  

  protected function appendModifersToFinalSignature(array $modifiersSignature, string $columSignature)
  {
    $prefix = $this->schema['prefix'] ?? '$table->';
    $separator = $this->schema['separator'] ?? '->';
    $columSignature = $prefix . $columSignature;
    $modifiers = count($modifiersSignature) ? $separator . implode($separator, array_values($modifiersSignature)) : '';
    return $columSignature . $modifiers . ';';
  }
}
