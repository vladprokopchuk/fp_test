<?php

namespace FpDbTest;

use Exception;
use mysqli;

class Database implements DatabaseInterface {
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    /**
     * Replace placeholders with values
     *
     * @param string $query
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function buildQuery(string $query, array $params = []): string
    {
        $query = preg_replace_callback('/\{([^\{\}]+)\}/', function ($matches) use ($params) {
            if (in_array($this->skip(), $params, true))
            {
                return '';
            }

            return $matches[1];
        }, $query);

        // Placeholders replacing
        foreach ($params as $param)
        {
            if (is_array($param))
            {
                // Processing for ?# arrays and ?a
                if (!$this->isAssociativeArray($param))
                {
                    // Not assoc array with args
                    $query = preg_replace('/\?\#/', implode(', ', array_map(function ($id) {
                        return '`' . addslashes($id) . '`';
                    }, $param)), $query, 1);
                    $query = preg_replace('/\?a/', implode(', ', array_map(function ($value) {
                        return is_null($value) ? 'NULL' : (is_numeric($value) ? $value : "'" . addslashes($value) . "'");
                    }, $param)), $query, 1);
                } else
                {
                    // Assoc array with args
                    $assignments = array_map(function ($key, $value) {
                        $value = is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                        return '`' . addslashes($key) . '` = ' . $value;
                    }, array_keys($param), $param);
                    $query = preg_replace('/\?a/', implode(', ', $assignments), $query, 1);
                }
            } elseif (is_bool($param))
            {
                $query = preg_replace('/\?d/', (int)$param, $query, 1);
            } elseif (is_numeric($param))// ?d and $f replacement
            {
                if (filter_var($param, FILTER_VALIDATE_INT))
                {
                    $query = preg_replace('/\?d/', (int)$param, $query, 1);
                }else{
                    $query = preg_replace('/\?f/', (float)$param, $query, 1);
                }
            } else
            {
                if ($param !== $this->skip())
                {
                    if (strpos($query, '?#') !== false)
                    {
                        // Process  or single (not array replacement) ?#
                        $query = preg_replace('/\?\#/', '`' . addslashes($param) . '`', $query, 1);
                    } else
                    {
                        // Process for casual placeholder - ?
                        $query = preg_replace('/\?/', "'" . addslashes($param) . "'", $query, 1);
                    }
                }
            }
        }
        return $query;
    }

    /**
     * Avoid replacement placeholder string
     *
     * @return string
     */
    public function skip(): string
    {
        return 'PASS_AWAY';
    }


    /**
     * @param array $arr
     * @return bool
     */
    public function isAssociativeArray(array $arr): bool
    {
        $keys = array_keys($arr);
        foreach ($keys as $key) {
            if (!is_int($key)) {
                return true;
            }
        }
        return false;
    }
}
