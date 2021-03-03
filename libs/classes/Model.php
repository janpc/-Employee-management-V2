
<?php

abstract class Model
{
    protected $table;
    protected $DTO;

    function __construct($table, $DTO = false)
    {
        $this->database = Database::getPDO();
        $this->table = $table;
        $this->DTO = $DTO;
    }

    function getAll()
    {
        try {
            $stmt = $this->database->prepare("SELECT * FROM $this->table");
            $stmt->setFetchMode(PDO::FETCH_ASSOC);    
            $stmt->execute();
            $data = $stmt->fetchAll();
            $classData = array();
            foreach ($data as $register) {
                $registerClass = new $this->DTO;
                foreach ($register as $key=>$value) {
                    $camelKey = Converter::snakeToCamelCase($key);
                    $registerClass->$camelKey = $value;    
                }
                array_push($classData, $registerClass);
            }
        } catch (PDOException $e) {
            return false;
        }
        return $classData;
    }

    function getById($id)
    {
        try {
            $stmt = $this->database->prepare("SELECT * FROM $this->table WHERE id=$id");
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();
            $data = $stmt->fetch();
            $classData = new $this->DTO;
            foreach ($data as $key => $value) {
                $camelKey = Converter::snakeToCamelCase($key);
                $classData->$camelKey = $value;
            }
        } catch (PDOException $e) {
            return false;
        }
        return $classData;
    }

    function insert($object)
    {
        echo var_dump($object);
        $arrayToInsert = array();
        if(is_array($object)) {
            $iterable = $object;
        } else {
            $iterable = get_object_vars($object);
        }

        foreach ($iterable as $key => $value) {
            if($key == 'id') {
                continue;
            }
            $snakeKey = Converter::camelToSnakeCase($key);
            $arrayToInsert[$snakeKey] = $value;
        }

        echo var_dump($arrayToInsert);


        $keys = array_map(function($key) {
            return "$key";
        },array_keys($arrayToInsert));

        $bindedKeys = array_map(function($key) {
            return ":$key";
        },$keys);

        $keysStr = "(" . join(",", $keys) . ")";
        $bindedKeysStr = "(" . join(",", $bindedKeys) . ")";

        $stmt = $this->database->prepare("INSERT INTO $this->table $keysStr
        VALUES $bindedKeysStr");
        try {
            foreach($arrayToInsert as $key=>&$value) {
                $stmt->bindParam(":$key", $value);
            }
            $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }

    function update($object)
    {
        $arrayToInsert = array();
        foreach (get_object_vars($object) as $key => $value) {
            if($key == 'id') {
                continue;
            }
            $snakeKey = Converter::camelToSnakeCase($key);
            $arrayToInsert[$snakeKey] = $value;
        }

        $keys = array_map(function($key) {
            return "$key";
        },array_keys($arrayToInsert));

        $bindedKeys = array_map(function($key) {
            return "$key = :$key";
        },$keys);

        $str = join(" ", $bindedKeys);

        try {
            $stmt = $this->database->prepare("UPDATE $this->table $str WHERE id = $object->id");
            foreach($arrayToInsert as $key=>&$value) {
                $stmt->bindParam(":$key", $value);
            }
            $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }

    function delete($id)
    {
        try {
            $stmt = $this->database->prepare("DELETE FROM $this->table WHERE id=$id");
            $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }
}
