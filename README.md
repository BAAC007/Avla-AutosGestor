# Avila-AutosGestor

Plataforma web de gestión para un concesionario de coches, 
desarrollada como proyecto final del primer curso de Desarrollo 
de Aplicaciones Web (DAW).

🔗 **Demo en vivo:** https://avla-autosgestor.onrender.com/

## Descripción

La aplicación cuenta con dos áreas diferenciadas:

- **Front (usuario):** los clientes pueden registrarse, iniciar 
  sesión y editar la información de su perfil.
- **Back (administrador):** panel con operaciones CRUD completas 
  (crear, leer, actualizar, eliminar) para gestionar el catálogo 
  de vehículos en la base de datos.

## Tecnologías

- **Backend:** PHP
- **Frontend:** HTML, CSS, JavaScript
- **Base de datos:** MySQL (alojada en filess.io)
- **Despliegue:** Render (vía Docker)

## Funcionalidades

- Registro y edición de perfil de usuario
- Panel de administración con CRUD de vehículos
- Contraseñas protegidas mediante hash
- Configuración sensible gestionada mediante variables de entorno
- Aplicación desplegada y accesible públicamente

## Instalación local

1. Clona el repositorio:
```bash
   git clone https://github.com/BAAC007/Avla-AutosGestor.git
```
2. Crea un archivo `.env` en la raíz del proyecto basándote en `.env.example`
3. Configura tu base de datos MySQL con las credenciales correspondientes
4. Levanta el proyecto con tu servidor local (o mediante Docker, usando el `Dockerfile` incluido)

## Próximas mejoras

- [ ] Validaciones adicionales en formularios
- [ ] Panel de estadísticas para el administrador
- [ ] Mejoras de diseño responsive

## Autor

**Bryan Avila** — Estudiante de DAW  
[LinkedIn](https://www.linkedin.com/in/bryan-avila-12105b347/) · [Portafolio](https://baac007.github.io/Portafolio25/portafolio.html)
