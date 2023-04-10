<?php

namespace Bumba\Sql2Migration\Writer\traits;


trait SchemaConvention
{
  protected function getConventionByModifier(array &$column)
  {
    if (!count($column['modifiers-signatures']) || !isset($this->schema['conventions']['modifiers'])) return;
    $modifiersUsed = array_keys($column['modifiers-signatures']);

    foreach ($this->schema['conventions']['modifiers'] as $key => $variants) {
      if (in_array($key, $modifiersUsed)) {
        foreach ($variants as $variant => $signature) {
          if (str_contains($variant, '+')) {
            [$modifier, $dataType] = explode('+', $variant);

            if (in_array($modifier, $modifiersUsed)) {
              if ($column['datatype'] == $dataType) {
                unset($column['modifiers-signatures'][$key], $column['modifiers-signatures'][$modifier]);
                return $signature;
              }
            }
          } else {
            if ($column['datatype'] == $variant) {
              unset($column['modifiers-signatures'][$key]);
              return $signature;
            }
          }
        }
      }
    }

    return null;
  }
  

  protected function getConventionByDataType(array &$column)
  {
    if (!isset($this->schema['conventions']['datatypes'])) return;

    return $this->schema['conventions']['datatypes'][$column['datatype']][$column['datatype-arg']] ?? null;
  }


  protected function getFkConventionByAction(string $action, string $trigger)
  {
    return $this->schema['foreign Keys']['conventions'][$action][$trigger] ?? null;
  }
}
