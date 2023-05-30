<?php

namespace Source\Models;

use Source\Core\Model;

class Admin extends Model
{
  /** @var string $entity Database Table */
  protected static $entity = "admins";
  
  /** @var array $protected No Create or Update */
  protected static $protected = ["id", "created_at", "updated_at"];

  /** @var array $required  Required Fields */
  protected static $required = ["name", "email", "password"];

  /**
   * @var string $name
   * @var string $email
   * @var string $password
   * @return Admin
   */
  public function bootstrap(string $name, string $email, string $password): Admin
  {
    $this->name = $name;
    $this->email = $email;
    $this->password = $password;

    return $this;
  }

  /**
   * @var string $terms
   * @var string $params
   * @var string $columns
   * @return null|array
   */
  public function find(string $terms, string $params, string $columns = "*"): ?array
  {

    $find = $this->read("SELECT {$columns} FROM " . static::$entity . " WHERE {$terms}", $params);
    if ($this->fail() || !$find->rowCount()) {
      $this->message = "Nenhum cadastro encontrado para os dados informados";
      return null;
    }

    return $find->fetchAll(\PDO::FETCH_CLASS, __CLASS__);
  }

    /**
   * @var string $id
   * @var string $columns
   * @return null|Admin
   */
  public function findById(string $id, string $columns = "*"): ?Admin
  {
    $find = $this->read("SELECT {$columns} FROM " . static::$entity . " WHERE id = :id", "id={$id}");
    if ($this->fail() || !$find->rowCount()) {
      $this->message = "Nenhum usuário encontrado para o id informado";
      return null;
    }

    return $find->fetchObject(__CLASS__);
  }


  /**
   * @var string $email
   * @var string $columns
   * @return null|Admin
   */
  public function findByEmail(string $email, string $columns = "*"): ?Admin
  {
    $find = $this->read("SELECT {$columns} FROM " . static::$entity . " WHERE email = :email", "email={$email}");
    if ($this->fail() || !$find->rowCount()) {
      $this->message = "Nenhum usuário encontrado para o email informado";
      return null;
    }

    return $find->fetchObject(__CLASS__);
  }

  /**
   * @return null|Admin
   */
  public function save(): ?Admin
  {
    /** Email Validate */
    if (!is_email($this->email)) {
      $this->message = "O email informado não parece um email válido";
      return null;
    }

    /** Required Validate */
    if (!$this->required()) {
      $this->message = "Informe todos os dados para cadastrar";
      return null;
    }

    /** Admin Update */
    if (!empty($this->id)) {
      $adminId = $this->id;
      if ($this->find("email = :email AND id != :id", "email={$this->email}&id={$adminId}")) {
        $this->message = "O email informado já está cadastrado";
        return null;
      }

      $this->update(static::$entity, $this->safe(), "id = :id", "id={$adminId}", "id");
      if ($this->fail()) {
        $this->message = "Erro ao cadastrar, verifique os dados";
        return null;
      }

      $this->message = "Cadastrado atualizado com sucesso";
    }

    /** Admin Create */
    if (empty($this->id)) {
      if ($this->findByEmail($this->email)) {
        $this->message = "O email informado já está cadastrado";
        return null;
      }

      $adminId = $this->create(static::$entity, $this->safe());
      if ($this->fail()) {
        $this->message = "Erro ao cadastrar, verifique os dados";
        return null;
      }

      $this->message = "Cadastro realizado com Sucesso";
    }

    $this->data = ($this->findById($adminId))->data();
    return $this;
  }

  /**
   * @return array
   */
  protected function safe(): array
  {
    $data = (array)$this->data;
    foreach (static::$protected as $key => $value) {
      unset($data[$value]);
    }

    return $data;
  }

  /**
   * @return bool
   */
  protected function required(): bool
  {
    $data = (array)$this->data;
    foreach (static::$required as $key => $value) {
      if (empty($data[$value])) {
        return false;
      }
    }

    return true;
  }
}