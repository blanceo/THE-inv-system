<?php
   $host = "172.20.10.2";  // Your IP
   $username = "root";
   $password = "";
   $database = "your_database_name";
   
   $conn = new mysqli($host, $username, $password, $database);
   
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }
   echo "Connected successfully!";
   ?>