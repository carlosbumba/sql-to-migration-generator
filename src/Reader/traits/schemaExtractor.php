<?php

namespace Bumba\Sql2Migration\Reader\traits;

/**
 * A trait that provides methods for extracting information related to the schema of an SQL statement.
 */
trait schemaExtractor
{
    /**
     * Extracts the default character set from the SQL statement.
     *
     * @param string $line The line to search for the default character set.
     * @return string The default character set, or an empty string if not found.
     */
    protected function extractSchemaCharset(string $line)
    {
        if (stripos($line, 'DEFAULT CHARACTER SET') !== false) {
            $regex = "@DEFAULT\sCHARACTER\sSET\s([a-zA-Z0-9\-\_]+)\s+?@i";
            if (preg_match($regex, $line, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }

    /**
     * Extracts the current schema name and charset from the SQL statement.
     *
     * @param string $line The line to search for the schema name.
     * @return array An array containing the schema name and charset.
     * @throws \Exception if the schema name is not found.
     */
    protected function extractCurrentSchema(string $line)
    {
        $regex = "@\`([a-zA-Z0-9\_]+)\`@";
        $schema = null;

        // capture the schema name
        if (preg_match($regex, $line, $matches)) {
            $schema = $matches[1];
        }

        if (is_null($schema)) {
            throw new \Exception("Schema name is not inside ` `");
        }

        // capture charset if is present
        $charset = $this->extractSchemaCharset($line);

        return [
            'schema' => $schema,
            'charset' => $charset,
        ];
    }
}
