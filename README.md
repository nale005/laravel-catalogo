# Catálogo de Productos - Prueba Técnica

## Requerimientos

- Laravel 12
- Docker + Docker Compose
- MySQL

## Uso

1. Clonar el repositorio:
git clone https://github.com/nale005/laravel-catalogo.git
cd catalogo

2. Levantar los contenedores:
docker-compose up -d

3. Correr migraciones:
docker exec -it laravel_app bash
php artisan migrate

4. Acceder en el navegador:
http://localhost:8000/productos

## Notas

- La app permite crear, editar, eliminar productos.
- Todas las validaciones requeridas se implementan en JS y en el backend.
- Las imágenes se almacenan en `public/uploads`.