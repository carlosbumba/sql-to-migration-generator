<?php

namespace Bumba\Sql2Migration\Reader\traits;


trait ColumnExtractor
{
  protected string $typeRegex = '(?<datatype>[a-z][a-z0-9\_]+)';
  protected string $ArgRegex = '\s?\((?<arg>[a-z0-9\s\'\,\_\"]+)\)';

  protected function extractDataType(string $definition): string|array
  {
    $datatype = [];
    $arg = null;

    // regexp for datatype with arguments
    $regexp = "@{$this->typeRegex}{$this->ArgRegex}@i";

    if (preg_match($regexp, $definition, $matches)) {
      $datatype = $matches['datatype'];
      $arg = $this->getDataTypeArg($matches['arg']);
    } else {
      $datatype = explode(' ', $definition, 2)[0];
    }

    $datatype = strtolower($datatype);

    return [$datatype, $arg];
  }


  protected function getDataTypeArg(string $arg)
  {
    if (stripos($arg, ',') !== false) {
      $arg = array_map(fn ($a) => ctype_digit($a) ? intval($a) : $a, explode(',', $arg));
    } elseif (ctype_digit($arg)) {
      $arg = intval($arg);
    }

    return $arg;
  }


  protected function extractColumnModifier(string $definition)
  {
    $modifiers = [];
    $modifiers['unsigned'] = stripos($definition, ' unsigned') !== false;
    $modifiers['nullable'] = stripos($definition, 'not null') === false;
    $modifiers['default'] = null;

    if (stripos($definition, ' default') !== false) {
      $regex = '@\sdefault\s?\(?([a-z0-9\_\-\.\'\"\/]+)\)?\s?@i';

      if (preg_match($regex, $definition, $matches)) {
        // if the default value is a function call like: getdate()
        if (stripos($definition, "{$matches[1]}()") !== false) {
          $matches[1] = "{$matches[1]}()";
        }

        $modifiers['default'] = $matches[1];
      }
    }

    $modifiers['primary'] = stripos($definition, ' primary key') !== false;

    return $modifiers;
  }


  protected function columnMutator(array &$column)
  {
    $column['datatype'] = $column['datatype'] == 'int' ? 'integer' : $column['datatype'];

    if (in_array($column['datatype'], ['set', 'enum']) and !is_null($column['modifiers']['default'])) {
      if (ctype_digit($column['modifiers']['default'])) {
        $column['modifiers']['default'] = "'{$column['modifiers']['default']}'";
      }
    }
  }

  protected function extractCurrentColumn(string $definition)
  {
    $regex = "@\`?([a-zA-Z0-9\_]+)\`?\s?@";
    $info = [];

    if (preg_match($regex, $definition, $matches)) {
      $info['name'] = $matches[1];
      $info['str-name'] = "'{$matches[1]}'";
      // remove the column name in definition text
      $textAfterColumn = trim(str_replace($matches[0], '', $definition));
      // get the datatype and his argument
      list($info['datatype'], $info['datatype-arg']) = $this->extractDataType($textAfterColumn);
      //  get column modifiers
      $info['modifiers'] = $this->extractColumnModifier($definition);
      // mutate the column if needed
      $this->columnMutator($info);
    }

    return $info;
  }
}
