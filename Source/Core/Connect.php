<?php

namespace Source\Core;

use \PDO;
use \PDOException;

class Connect
{
  /** Configuração das opções do PDO */
  private const OPTION = [
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_CASE => PDO::CASE_NATURAL
  ];

  /** Propriedade estática que recebe a instância do objeto PDO */
  private static $instance;

  /**
   * @return null|PDO
   */
  public static function getInstance(): ?PDO
  {
    try {
      if (empty(static::$instance)) {
        static::$instance = new PDO(
          "mysql:host=" . CONF_DB_HOST . ";dbname=" . CONF_DB_NAME,
          CONF_DB_USER,
          CONF_DB_PASS,
          self::OPTION
        );
      }

      return static::$instance;
    } catch (PDOException $exception) {
      var_dump($exception);
    }
  }

  public function __construct()
  {
  }

  public function __clone()
  {    
  }
}