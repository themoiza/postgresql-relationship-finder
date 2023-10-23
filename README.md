# Postgresql Relationship Finder

Postgresql Relationship Finder is a tool for discovering and visualizing the relationships between database tables in PostgreSQL.

## Table of Contents

- [Features](#features)
- [Getting Started](#getting-started)
- [Prerequisites](#prerequisites)
- [Installation](#installation)

## Features

- Discover the relationships between tables using foreign keys.
- Easily navigate complex database schemas.
- Optimize SQL queries by understanding data relationships.

## Getting Started

```php
#!/usr/bin/php -q
<?php

require_once 'vendor/autoload.php';

use TheMoiza\PostgresqlRelationshipFinder\RelationshipFinder;

$RelationshipFinder = new RelationshipFinder;

echo $RelationshipFinder->find(
	$tableDown = ['public' => 'users'],
	$tableTop = ['public' => 'budget'],
	$connection = [
		"DB_HOST" => "127.0.0.1",
		"DB_PORT" => "5432",
		"DB_DATABASE" => "database",
		"DB_USERNAME" => "user",
		"DB_PASSWORD" => "pass",
		"DB_SCHEMA" => "public"
	]
);
```

### Prerequisites

Before you begin, ensure you have met the following requirements:

- PostgreSQL installed and configured.
- PHP for running the Postgresql Relationship Finder script.

### Installation

1. Clone this repository.
   ```sh
   git clone https://github.com/themoiza/postgresql-relationship-finder.git
   ```
