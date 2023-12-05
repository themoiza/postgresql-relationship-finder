<?php

namespace TheMoiza\PostgresqlRelationshipFinder;

class RelationshipFinder
{
	protected object|bool $_pdo = false;

    protected string $_upSchema = 'public';

    protected string $_upTable = '';

    protected string $_downSchema = 'public';
    
    protected string $_downTable = '';

    protected array $_fks = [];

    protected array $_relations = [];

	protected function _connectPgsql(array $dbConnection) :object
	{

		$this->_dbConnection = $dbConnection;

		try{

			$dsn = $this->_dbConnection['DB_CONNECTION']??'pgsql'.':host='.$this->_dbConnection['DB_HOST'].';port='.$this->_dbConnection['DB_PORT'].';dbname='.$this->_dbConnection['DB_DATABASE'];
			$this->_pdo = new \PDO($dsn, $this->_dbConnection['DB_USERNAME'], $this->_dbConnection['DB_PASSWORD'], []);
			$this->_pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 1);

		}catch(\PDOException $e){

			throw new RelationshipFinderException($e->getMessage());
		}

		return $this;
	}

	protected function _setConfig(bool|array $dbConnection = false) :object
	{

        if(is_array($dbConnection)){
            $this->_connectPgsql($dbConnection);
        }

		return $this;
	}

    protected function _findPaths($constraints, $schemaLeft, $tableLeft, $schemaRight, $tabelaRight, $currentPath = []) :array{

        $currentPath[] = $schemaLeft.'.'.$tableLeft;

        if ($schemaLeft.'.'.$tableLeft === $schemaRight.'.'.$tabelaRight) {
            return [$currentPath];
        }

        $paths = [];

        foreach ($constraints as $l) {

            $downSchema = $l['down_schema'];
            $downTable = $l['down_table'];
            //$downColumn = $l['down_column'];
            $upSchema = $l['up_schema'];
            $upTable = $l['up_table'];
            //$upColumn = $l['up_column'];

            if ($downSchema.'.'.$downTable === $schemaLeft.'.'.$tableLeft) {

                $possiblePath = $currentPath;

                if (!in_array($upSchema.'.'.$upTable, $possiblePath)) {

                    $findPath = $this->_findPaths($constraints, $upSchema, $upTable, $schemaRight, $tabelaRight, $possiblePath);
                    foreach ($findPath as $line) {
                        $paths[] = $line;
                    }
                }
            }
        }

        return $paths;
    }

    protected function _query() :array{
        
        $query = $this->_pdo->query("select
                keylist.table_schema as down_schema,
                keylist.table_name as down_table,
                down_info.column_name as down_column,
                upref.unique_constraint_schema as up_schema,
                up_info.table_name as up_table,
                up_info.column_name as up_column
            from information_schema.table_constraints keylist
            join information_schema.key_column_usage down_info
            on keylist.constraint_schema = down_info.constraint_schema
            and keylist.constraint_name = down_info.constraint_name
            join information_schema.referential_constraints upref
            on keylist.constraint_schema = upref.constraint_schema
            and keylist.constraint_name = upref.constraint_name
            join information_schema.key_column_usage up_info
            on upref.unique_constraint_schema = up_info.constraint_schema
            and upref.unique_constraint_name = up_info.constraint_name
            where keylist.constraint_type = 'FOREIGN KEY'");

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(array $tableDown, array $tableUp, bool|array $dbConnection = false) :string{

		if(is_array($tableDown)){
			$this->_downSchema = key($tableDown);
			$this->_downTable = $tableDown[$this->_downSchema];
		}

		if(is_array($tableUp)){
			$this->_upSchema = key($tableUp);
			$this->_upTable = $tableUp[$this->_upSchema];
		}

        if(is_array($dbConnection)){
            $this->_setConfig($dbConnection);
        }

        $this->_fks = $this->_query();

        $paths = $this->_findPaths($this->_fks, $this->_downSchema, $this->_downTable, $this->_upSchema, $this->_upTable);

        $title = $this->_downSchema.'.'.$this->_downTable.' AND '.$this->_upSchema.'.'.$this->_upTable;

        if(count($paths) == 0){

            return 'Não há caminhos entre '.$title;
        }

        $str = 'PATH(S) BETWEEN '.$title.":\n";

        foreach($paths as $path){
            $str .= '··'.implode(' --> ', $path)."\n";
        }

        return $str;
    }

    public function allTo($schema, $table, bool|array $dbConnection = false) :string{

        if(is_array($dbConnection)){
            $this->_setConfig($dbConnection);
        }

        $this->_upSchema = $schema;
        $this->_upTable = $table;

        $this->_fks = $this->_query();

        $paths = [];

        foreach($this->_fks as $line){

            $this->_downSchema = $line['down_schema'];
            $this->_downTable = $line['down_table'];

            $temp = $this->_findPaths($this->_fks, $this->_downSchema, $this->_downTable, $this->_upSchema, $this->_upTable);

            if(count($temp) > 0){

                $paths = array_merge($paths, $temp);
            }
        }

        $title = $this->_upSchema.'.'.$this->_upTable;

        if(count($paths) == 0){

            return 'Não há caminhos que levam a '.$title;
        }

        $str = 'Todos os caminhos para '.$title.":\n";

        foreach($paths as $path){
            $str .= implode(' --> ', $path)."\n";
        }

        return $str;
    }

    public function allFrom($schema, $table, bool|array $dbConnection = false) :string{

        if(is_array($dbConnection)){
            $this->_setConfig($dbConnection);
        }

        $this->_downSchema = $schema;
        $this->_downTable = $table;

        $this->_fks = $this->_query();

        $paths = [];

        foreach($this->_fks as $line){

            $this->_upSchema = $line['up_schema'];
            $this->_upTable = $line['up_table'];

            $temp = $this->_findPaths($this->_fks, $this->_downSchema, $this->_downTable, $this->_upSchema, $this->_upTable);

            if(count($temp) > 0){

                $paths = array_merge($paths, $temp);
            }
        }

        $title = $this->_upSchema.'.'.$this->_upTable;

        if(count($paths) == 0){

            return 'Não há caminhos que levam a '.$title;
        }

        $str = 'Todos os caminhos para '.$title.":\n";

        foreach($paths as $path){
            $str .= implode(' => ', $path)."\n";
        }

        return $str;
    }
}