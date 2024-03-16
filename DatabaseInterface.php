<?php

namespace FpDbTest;

interface DatabaseInterface
{
    public function buildQuery(string $query, array $params = []): string;

    public function skip();
}
