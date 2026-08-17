<?php
$hash = '$2y$12$J.lAw4ePlMHUE/UsOFypSelKW43XAWqple4hqoBaLr16FddKfnyGy';
$password = '12345678';
if (password_verify($password, $hash)) {
    echo "Password MATCHES!\n";
} else {
    echo "Password DOES NOT MATCH!\n";
}
