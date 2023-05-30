<?php

namespace Source\Core;

use PDOException;
use PDOStatement;
use stdClass;

abstract class Model
{
  /** @var null|stdClass */
  protected $data;
  
  /** @var null|string */
  protected $message;

  /** @var null|PDOException */
  protected $fail;

  /**
   * @return null!stdClass
   */
  public function data(): ?stdClass
  {
    return $this->data;
  }

  /**
   * @return null!string
   */
  public function message(): ?string
  {
    return $this->message;
  }

  /**
   * @return null!PDOException
   */
  public function fail(): ?PDOException
  {
    return $this->fail;
  }

  public function __set($name, $value)
  {
    if (empty($this->data)) {
      $this->data = new stdClass();
    }

    $this->data->$name = $value;
  }

  public function __get(string $name)
  {
    return ($this->data->$name ?? null);
  }

  public function __isset($name)
  {
    return isset($this->data->$name);
  }

  protected function create(string $entity, array $data): ?int
  {
    try {
      $columns = implode(", ", array_keys($data));
      $values = ":" . implode(", :", array_keys($data));
      $stmt = Connect::getInstance()->prepare("INSERT INTO {$entity} ({$columns}) VALUES ({$values})");
      $stmt->execute($this->filter($data));

      return Connect::getInstance()->lastInsertId();
    } catch (PDOException $exception) {
      $this->fail = $exception;
      return null;
    }
  }

  /**
   * @return PDOStatement
   * @var string $select
   * @var string $params
   */
  protected function read(string $select, $params = null): PDOStatement
  {
    try {
      $stmt = Connect::getInstance()->prepare($select);
      if ($params) {
        parse_str($params, $params);
        foreach ($params as $key => $value) {
          $type = (is_numeric($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
          $stmt->bindValue(":{$key}", $value, $type);
        }
      }

      $stmt->execute();
      return $stmt;
    } catch (PDOException $exception) {
      $this->fail = $exception;
    }
  }

  /**
   * @return null|int
   * @var string $entity
   * @var array $data
   * @var string $terms
   * @var string $params
   */
  protected function update(string $entity, array $data, string $terms, string $params): ?int
  {
    try {

      $columns = [];
      foreach ($data as $key => $value) {
        $columns[] = $key . " = " . ":" . $key;
      }
      $columns = implode(", ", $columns);
      parse_str($params, $params);
      $data = array_merge($data, $params);
      $stmt = Connect::getInstance()->prepare("UPDATE {$entity} SET {$columns} WHERE {$terms}");
      $stmt->execute($this->filter($data));

      return ($stmt->rowCount() ?? 1);

    } catch (PDOException $exception) {
      $this->fail = $exception;
      return null;
    }
  }

  /**
   * @return null|int
   * @var string $entity
   * @var string $terms
   * @var string $params
   */
  protected function delete(string $entity, string $terms, string $params): ?int
  {
    try {
      $stmt = Connect::getInstance()->prepare("DELETE FROM {$entity} WHERE {$terms}");
      parse_str($params, $params);
      $stmt->execute($params);
      return ($stmt->rowCount() ?? 1);
      die;
    } catch (PDOException $exception) {
      $this->fail = $exception;
      return null;
    }
  }

  /**
   * @return array
   * @var array $data
   */
  private function filter(array $data): array
  {
    return $filter = filter_var_array($data, FILTER_SANITIZE_SPECIAL_CHARS);
  }
}