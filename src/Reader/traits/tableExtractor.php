<?php

namespace Bumba\Sql2Migration\Reader\traits;

/**
 * A trait that provides a method to extract the name of the table from an SQL statement.
 */
trait tableExtractor
{

    /**
     * Extracts the name of the current table from the SQL statement.
     *
     * @param string $line The line to search for the table name.
     * @return string The name of the current table.
     * @throws \Exception if the table name is not found.
     */
    protected function extractCurrentTable(string $line)
    {
        $table = null;
        $text = str_replace('`', '', $line);
        $regex = "@\s?([a-zA-Z0-9_\.]+)\s+?\(@";

        if (preg_match($regex, $text, $matches)) {
            $table = $matches[1];

            if (strpos($table, '.')) {
                $table = explode('.', $table)[1];
            }
        }

        if (is_null($table)) {
            throw new \Exception("table name not found");
        }

        return $table;
    }
}
