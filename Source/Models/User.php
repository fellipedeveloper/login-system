<?php

namespace Source\Models;

use PDOException;
use Source\Core\Model;

class User extends Model
{
  /** @var string $entity Database Table */
  protected static $entity = "users";

  /** @var array $protected No Create or Update */
  protected static $protected = ["id", "created_at", "updated_at"];

  /** @var array $required  Required Fields*/
  protected static $required = ["name", "email"];

  /**
   * @var string $name
   * @var string $email
   * @return User
   */
  public function bootstrap(string $name, string $email): User
  {
    $this->name = $name;
    $this->email = $email;
    
    return $this;
  }

  /**
   * @return null|array
   * @var string $terms
   * @var string $params 
   * @var string $columns 
   */
  public function find(string $terms, string $params, string $columns = "*"): ?array
  {
    $find = $this->read("SELECT {$columns} FROM " . static::$entity . " WHERE {$terms}", $params);
    if ($this->fail() || !$find->rowCount()) {
      $this->message = "Nenhum usuário encontrado para os dados informados";
      return null;
    }

    return $find->fetchAll(\PDO::FETCH_CLASS, __CLASS__);
  }

  /**
   * @return null|User
   * @var int $id
   * @var string @columns
   */
  public function findById(int $id, string $columns = "*"): ?User
  {
    $stmt = $this->read("SELECT {$columns} FROM " . static::$entity . " WHERE id = :id", "id={$id}");
    if ($this->fail() || !$stmt->rowCount()) {
      $this->message = "Nenhum usuário encontrado para o id informado";
      return null;
    }

    return $stmt->fetchObject(__CLASS__);
  }

  /**
   * @return null|User
   * @var string $email
   * @var string $columns
   */
  public function findByEmail(string $email, string $columns = "*"): ?User
  {
    $stmt = $this->read("SELECT {$columns} FROM " . static::$entity . " WHERE email = :email", "email={$email}");
    if ($this->fail() || !$stmt->rowCount()) {
      $this->message = "Nenhum usuário encontrado para o email informado";
      return null;
    }

    return $stmt->fetchObject(__CLASS__);
  }

  /**
   * @return null|array
   * @var int $limit
   * @var int @offset
   * @var string @columns
   */
  public function all(int $limit = 30, int $offset = 0, $columns = "*"): ?array
  {
    $stmt = $this->read("SELECT {$columns} FROM " . static::$entity . " LIMIT :limit OFFSET :offset", "limit={$limit}&offset={$offset}");
    if ($this->fail() || !$stmt->rowCount()) {
      $this->message = "Nenhum usuário encontrado";
      return null;
    }
    var_dump($stmt);
    return $stmt->fetchAll(\PDO::FETCH_CLASS, __CLASS__);
  }

  /**
   * @return null|User
   */
  public function save(): ?User
  {
    /** Validação de campos obrigatórios */
    if (!$this->required()) {
      $this->message = "Nome, sobrenome e email são obrigatórios";
      return null;
    }

    /** Validação de email */
    if (!is_email($this->email)) {
      $this->message = "E email informado não parece válido";
      return null;
    }

    /** Atualização de cadastro de usuário */
    if(!empty($this->id)) {

      $userId = $this->id;

      /** Validação de email único */
      if($this->find("email = :email AND id != :id", "email={$this->email}&id={$userId}", "id")) {
        $this->message = "O email informado já está cadastrado";
        return null;
      }

      $update = $this->update(static::$entity, $this->safe(), "id = :id", "id={$userId}");
      if ($this->fail()) {
        $this->message = "Não foi possível atualizar, verifique os dados!";
        return null;
      }

      $this->message = "Cadastrado atualizado com sucesso";
    }

    /** Cadastro de usuário */
    if(empty($this->id)) {
      /** Validação de email único*/
      if ($this->findByEmail($this->email)) {
        $this->message = "O email informado já está cadastrado";
        return null;
      }

      $userId = $this->create(static::$entity, $this->safe());
      if ($this->fail()) {
        $this->message = "Erro ao cadastrar, verifique os dados";
        return null;
      }
      
      $this->message = "Cadastro realizado com sucesso";
    }

    $this->data = ($this->findById($userId))->data();
    return $this;
  }

  /**
   * @return null|User
   */
  public function destroy(): ?User
  {
    if (!empty($this->id)) {
      $this->delete(static::$entity, "id = :id", "id={$this->id}");
    }

    if ($this->fail()) {
      $this->message = "Erro ao excluir, verifique os dados";
      return null;
    }

    $this->data = null;
    return $this;
  }

  /**
   * @return array
   */
  protected function safe(): array
  {
    $data = (array)$this->data();
    foreach (static::$protected as $key => $value) {
      unset($data[$value]);
    }

    return $data;
  }

  /**
   * @return boolean
   */
  protected function required(): bool
  {
    $data = (array)$this->data();
    foreach (static::$required as $key => $value) {
      if(empty($data[$value])) {
        return false;
      }
    }

    return true;
  }
}