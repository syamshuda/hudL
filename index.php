<?php
$alt = __DIR__ . '/index (1).php';
if (file_exists($alt)) {
  require_once $alt;
} else {
  header('Location: login.php');
  exit();
}