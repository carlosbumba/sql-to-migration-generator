<?php

namespace Bumba\Sql2Migration\Writer\traits;

trait SignatureParams
{

    protected function getParams(string $signature): array
    {
        $params = [];

        $regex = '@(\$[a-z]+(?:\=[a-z0-9\"\']+)?)@';

        if (preg_match_all($regex, $signature, $matches)) {
            foreach ($matches[1] as $match) {
                if (str_contains($match, '=')) {
                    $parts = explode('=', $match);
                    if (ctype_digit($parts[1])) {
                        $parts[1] = intval($parts[1]);
                    }

                    $params[] = [$match, ...$parts];
                } else {
                    $params[] = $match;
                }
            }
        }

        return $params;
    }

    /**
     * Removes a parameter from the given signature.
     *
     * @param string $key The parameter key to remove.
     * @param string $signature The signature to remove the parameter from.
     * @return string The signature with the parameter removed.
     */
    protected function removeParam(string $key, string $signature): string
    {
        // Create an array of possible replacements for the parameter.
        $replaces = [",{$key}", ", {$key}", $key];

        // If the parameter appears at the beginning of a function call,
        // add another possible replacement to remove it.
        if (str_contains($signature, "({$key}")) {
            array_unshift($replaces, "{$key},");
        }

        // Replace all occurrences of the parameter with an empty string
        // to remove it from the signature.
        return str_replace($replaces, '', $signature);
    }

    /**
     * Replaces parameters in a given signature string with values.
     *
     * @param array  $values    An array of values to use for replacing parameters.
     * @param string $signature The signature string with parameters to be replaced.
     *
     * @return string The signature string with replaced parameters.
     */
    protected function replaceParams(array $values, string $signature): string
    {
        $params = $this->getParams($signature);
        $paramsCount = count($params);

        for ($index = 0; $index < $paramsCount; $index++) {
            $param = $params[$index];
            $key = is_array($param) ? $param[0] : $param;
            $default = is_array($param) ? $param[2] : null;

            if (!array_key_exists($index, $values) && $default !== null) {
                // If the value for this parameter is not provided and there is a default value,
                // remove the parameter from the signature.
                $signature = $this->removeParam($key, $signature);
                continue;
            }

            $value = $values[$index] ?? null;
            $nextValue = $values[$index + 1] ?? null;

            if ($value === null) {
                // If the value for this parameter is not provided, continue to the next parameter.
                continue;
            }

            $nextParam = $params[$index + 1] ?? null;
            $nextDefault = is_array($nextParam) ? $nextParam[2] : null;

            // Determine if this parameter is the last one, if there is no next value, and if the
            // next parameter will be removed (i.e., it has no value and there is a default value).
            $isLastParam = ($index + 1) === $paramsCount;
            $noNextValue = $nextValue === null;
            $nextValueIsDefault = $nextValue === $nextDefault;

            if ($value === $default && $isLastParam || $value === $default && $noNextValue && $nextValueIsDefault) {
                // If the value for this parameter is the same as the default value and this is the last
                // parameter or the next parameter will be removed, remove this parameter from the signature.
                $signature = $this->removeParam($key, $signature);
            } else {
                // Replace the parameter placeholder with the actual value.
                $signature = str_replace($key, $value, $signature);
            }
        }

        return $signature;
    }
}
