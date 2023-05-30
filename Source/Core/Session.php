<?php

namespace Source\Core;

class Session
{
  public function __construct()
  {
    if (!session_id()) {
      session_start();
    }
  }

  /**
   * @var string $name
   * @return null|object
   */
  public function __get($name): ?object
  {
    if(!empty($_SESSION[$name])) {
      return $_SESSION[$name];
    }

    return null;
  }

  /**
   * @var string $name
   * @return bool
   */
  public function __isset(string $name): bool
  {
    return $this->has($name);
  }

  public function all()
  {
    return (object)$_SESSION;
  }

  /**
   * @var string $key
   * @var mixed $value
   * @return Session
   */
  public function set(string $key, mixed $value): Session
  {
    $_SESSION[$key] = (is_array($value) ? (object)$value : $value);
    return $this;
  }

  /**
   * @var string $key
   * @return Session
   */
  public function unset(string $key): Session
  {
    unset($_SESSION[$key]);
    return $this;
  }

  /**
   * @var string $key
   * @return bool
   */
  public function has(string $key): bool
  {
    return isset($_SESSION[$key]);
  }

  /**
   * @return Session
   */
  public function regenerate(): Session
  {
    session_regenerate_id(true);
    return $this;
  }

  /**
   * @return Session
   */
  public function destroy(): Session
  {
    session_destroy();
    return $this;
  }
}