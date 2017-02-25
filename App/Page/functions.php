<?php
declare(strict_types = 1);

function target(string $query): string {
	parse_str($query, $parsedQuery);
	return '?' . http_build_query($parsedQuery + $_GET, '', '&', PHP_QUERY_RFC3986);
}