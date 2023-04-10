<?php

namespace Bumba\Sql2Migration\Writer\traits;

trait ForeignKeyParser
{
/**
 * Parse a foreign key column and return its formatted signature.
 *
 * @param array $foreignKey The foreign key column to be parsed.
 *
 * @return string The formatted signature of the foreign key column.
 */
    protected function parseForeignKey(array $foreignKey)
    {
        $signature = $this->schema['foreign Keys']['format'];

        $args = $this->getForeignKeyArgsForReplacement($foreignKey);

        $foreignKey['signature'] = $this->replaceParams($args, $signature);
        $this->tryGetFkSchemaConvention($foreignKey);

        if (!isset($foreignKey['has-convention'])) {
            $foreignKey['signature'] .= $this->getTriggersModifiers($foreignKey['triggers']);
        }

        return ($this->schema['prefix'] ?? '$table->') . $foreignKey['signature'] . ';';
    }

    protected function getForeignKeyArgsForReplacement(array $fk)
    {
        $arguments = [
            $fk['field'],
            $fk['name'],
            $fk['references']['field'],
            $fk['references']['table'],
        ];

        return $arguments;
    }

    /**
     * Tries to get the foreign key convention from the schema and adds it to the foreign key signature.
     *
     * @param array &$fk The foreign key array to modify.
     * @return void
     */
    protected function tryGetFkSchemaConvention(array&$fk)
    {
        foreach ($fk['triggers'] as $key => $value) {
            $signature = $this->getFkConventionByAction($value, $key);
            if ($signature) {
                $fk['signature'] .= ($this->schema['separator'] ?? '->') . $signature;
                $fk['has-convention'] = true;
            } else {
                $fk['signature'] .= $this->getTriggerModifier($key, $value);
            }
        }
    }

    protected function getTriggersModifiers(array $triggers)
    {
        $modifiers = '';

        foreach ($triggers as $trigger => $value) {
            $modifiers .= $this->getTriggerModifier($trigger, $value);
        }

        return $modifiers;
    }

    protected function getTriggerModifier(string $trigger, string $value)
    {
        $modifier = '';

        if (isset($this->schema['foreign Keys']['triggers'][$trigger])) {
            $fn = $this->schema['foreign Keys']['triggers'][$trigger];
            $signature = $this->replaceParams(["'{$value}'"], $fn);
            $modifier = ($this->schema['separator'] ?? '->') . $signature;
        }

        return $modifier;
    }
}
