<?php
/**
 * INDEX SIMPLE PARA PRUEBAS
 * Solo muestra que PHP funciona
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>MediCareOnline - Test</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head>";
echo "<body>";
echo "<div class='container mt-5'>";
echo "<h1>🏥 MediCareOnline</h1>";
echo "<p>PHP está funcionando correctamente</p>";
echo "<div class='alert alert-success'>";
echo "Versión de PHP: " . phpversion();
echo "</div>";
echo "<p><a href='check_errors.php' class='btn btn-primary'>Ver Diagnóstico Completo</a></p>";
echo "</div>";
echo "</body>";
echo "</html>";
?>
