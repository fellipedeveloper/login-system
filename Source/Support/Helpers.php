<?php

##################
### VALIDATION ###
##################

/**
 * @return boolean
 * @var string $email
 */
function is_email(string $email): bool
{
  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return true;
  }

  return false;
}