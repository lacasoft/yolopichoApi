# YoLoPicho API

La API YoLoPicho es una solución desarrollada utilizando el microframework Slim 4, diseñada para facilitar y gestionar donaciones de platillos de comida a personas en situación vulnerable. Esta plataforma actúa como un puente entre donantes, que pueden ser restaurantes o individuos, y las comunidades que más lo necesitan.

## Comenzando

Estas instrucciones te permitirán obtener una copia del proyecto en funcionamiento en tu máquina local para propósitos de desarrollo y pruebas. Sigue las siguientes instrucciones para configurar tu entorno de desarrollo.

### Prerrequisitos

Necesitas tener instalado PHP (versión 8.1 o superior) y Composer para instalar las dependencias de Slim 4. Además, necesitarás acceso a una base de datos MySQL o compatible.

### Instalación

1. Clona el repositorio en tu máquina local:

git clone https://github.com/tu-usuario/platidonaciones-api.git

2. Navega al directorio del proyecto:

cd yolopichoApi

3. Instala las dependencias de PHP con Composer:

composer install

4. Crea un archivo `.env` en el directorio raíz del proyecto y ajusta tus variables de entorno.

5. Crea la estructura de base de datos

### Uso

Para iniciar el servidor de desarrollo local, ejecuta:

php -S localhost:8080 -t public

Navega a `http://localhost:8080` en tu navegador para ver la API en acción.

## Licencia

Este proyecto está bajo la Licencia MIT - ve el archivo [LICENSE.md](LICENSE.md) para detalles.

## Agradecimientos

- A todo el equipo de desarrolladores que contribuyó a este proyecto.
- A los donantes y voluntarios que hacen posible nuestra misión.
- A la comunidad Slim 4 por su excelente documentación y soporte.
