@echo off
title Lanzador de Servidor Local - Citas Medicas
cd /d "%~dp0"

echo ====================================================
echo           Levantando el entorno portable            
echo ====================================================
echo Abriendo el navegador en http://localhost:8080...

:: Abre el navegador de forma asíncrona
start http://localhost:8080

:: Ejecuta el servidor PHP usando la ruta absoluta interna
".\php\php.exe" -S localhost:8080

echo.
echo El servidor se ha detenido.
pause