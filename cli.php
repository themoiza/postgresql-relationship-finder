#!/usr/bin/php -q
<?php

require_once 'vendor/autoload.php';

use TheMoiza\PostgresqlRelationshipFinder\RelationshipFinder;

$RelationshipFinder = new RelationshipFinder;

echo $RelationshipFinder->find(
	$tableDown = ['public' => 'order'],
	$tableTop = ['public' => 'users'],
	$connection = [
		"DB_HOST" => "127.0.0.1",
		"DB_PORT" => "5432",
		"DB_DATABASE" => "database",
		"DB_USERNAME" => "user",
		"DB_PASSWORD" => "123",
		"DB_SCHEMA" => "public"
	]
);