<?php

return [
  'datatypes' => [
    'date' => 'date($name)',
    'time' => 'time($name, $precision=0)',
    'datetime' => 'dateTime($name)',
    'year' => 'year($name)',
    'timestamp' => 'timestamp($name, $precision=0)',
    'bigint' => 'bigInteger($name)',
    'integer' => 'integer($name)',
    'mediumint' => 'mediumInteger($name)',
    'smallint' => 'smallInteger($name)',
    'tinyint' => 'tinyInteger($name)',
    'enum' => 'enum($name, $list)',
    'set' => 'set($name, $list)',
    'double' => 'double($name, $precision=8, $scale=2)',
    'float' => 'float($name, $precision=8, $scale=2)',
    'char' => 'char($name, $length)',
    'varchar' => 'string($name, $length)',
    'tinytext' => 'tinyText($name, $length)',
    'mediumtext' => 'mediumText($name)',
    'text' => 'text($name)',
    'longtext' => 'longText($name)'
  ],
  'modifiers' => [
    'nullable' => 'nullable()',
    'unsigned' => 'unsigned()',
    'default' => 'default($value)',
    'primary' => 'primary()',
    'auto_increment' => 'autoIncrement()'
  ],
  'conventions' => [
    'modifiers' => [
      'auto_increment' => [
        'unsigned+bigint' => "id(\$name='id')",
        'unsigned+integer' => 'increments($name)',
        'unsigned+mediumint' => 'mediumIncrements($name)',
        'unsigned+smallint' => 'smallIncrements($name)',
        'unsigned+tinyint' => 'tinyIncrements($name)'
      ],
      'unsigned' => [
        'bigint' => 'unsignedBigInteger($name)',
        'integer' => 'unsignedInteger($name)',
        'mediumint' => 'unsignedMediumInteger($name)',
        'tinyint' => 'unsignedTinyInteger($name)',
        'float' => 'unsignedFloat($name,$precision=8,$scale=2)',
        'decimal' => 'unsignedDecimal($name,$precision=8,$scale=2)',
        'double' => 'unsignedDouble($name,$precision=8,$scale=2)'
      ],
    ],
    'datatypes' => [
      'char' => [
        36 => "uuid(\$name='uuid')",
        26 => "ulid(\$name='ulid')",
      ]
    ]
  ],
  'foreign Keys' => [
    'format' => 'foreign($column, $name)->references($ref)->on($table)',
    'triggers' => [
      'delete' => 'OnDelete($action)',
      'update' => 'OnUpdate($action)'
    ],
    'conventions' => [
      'no action' => [
        'delete' => 'noActionOnDelete()'
      ],
      'set null' => [
        'delete' => 'nullOnDelete()',
      ],
      'cascade' => [
        'delete' => 'cascadeOnDelete()',
        'update' => 'cascadeOnUpdate()'
      ],
      'restrict' => [
        'delete' => 'restrictOnDelete()',
        'update' => 'restrictOnUpdate()'
      ]
    ]
  ],
  'indexes' => [
    'basic' => 'index($column, $name=null, $algorithm=null)',
    'unique' => 'unique($column, $name=null, $algorithm=null)',
    'fulltext' => 'fullText($column, $name=null, $algorithm=null)',
    'spatial' => 'spatialIndex($column, $name=null, $algorithm=null)'
  ]
];
