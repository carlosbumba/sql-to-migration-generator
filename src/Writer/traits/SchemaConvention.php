<?php

namespace Bumba\Sql2Migration\Writer\traits;

trait SchemaConvention
{
    /**
     * Gets the convention signature for the column based on the modifiers used.
     *
     * @param array $column The column data.
     * @return string|null The convention signature, or null if no convention was found.
     */
    protected function getConventionByModifier(array&$column): ?string
    {
        $modifiersUsed = array_keys($column['modifiers-signatures']);

        if (!count($modifiersUsed) || !isset($this->schema['conventions']['modifiers'])) {
            return null;
        }

        foreach ($this->schema['conventions']['modifiers'] as $key => $variants) {
            if (!in_array($key, $modifiersUsed)) {
                continue;
            }

            foreach ($variants as $variant => $signature) {
                if (str_contains($variant, '+')) {
                    [$modifier, $dataType] = explode('+', $variant);

                    if (in_array($modifier, $modifiersUsed) && $column['datatype'] == $dataType) {
                        unset($column['modifiers-signatures'][$key], $column['modifiers-signatures'][$modifier]);
                        return $signature;
                    }
                } else {
                    if ($column['datatype'] == $variant) {
                        unset($column['modifiers-signatures'][$key]);
                        return $signature;
                    }
                }
            }
        }

        return null;
    }

    protected function getConventionByDataType(array&$column)
    {
        if (!isset($this->schema['conventions']['datatypes'])) {
            return;
        }

        return $this->schema['conventions']['datatypes'][$column['datatype']][$column['datatype-arg']] ?? null;
    }

    protected function getFkConventionByAction(string $action, string $trigger)
    {
        return $this->schema['foreign Keys']['conventions'][$action][$trigger] ?? null;
    }
}
