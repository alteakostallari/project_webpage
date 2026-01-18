<?php
session_start();
session_unset();//fshin variablat nga sessioni
session_destroy();
header("Location: login.php");
exit;
