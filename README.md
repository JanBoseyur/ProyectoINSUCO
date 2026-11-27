
# Proyecto: Plataforma de Soporte Técnico #

## Página web orientada a la gestión de tickets de atención técnica en instituciones educacionales ##

## Notificaciones por correo

Al crear un ticket, se envía un correo a cada usuario con rol `admin`. Los
cambios de estado no envían correos. Cada intento de notificación a los
administradores queda guardado en la tabla `notificacion`; un fallo SMTP no
deshace la creación del ticket.

1. Ejecuta `database/migrations/001_create_notificacion.sql` en `insuco_db`.
2. En hosting compartido, copia `config/mail.local.example.php` como
   `config/mail.local.php` y completa la clave SMTP. Ese archivo está excluido
   de Git y el directorio `config` bloquea el acceso web en Apache. Si el hosting
   ofrece variables de entorno para PHP, también puedes configurarlas allí;
   tienen prioridad sobre `mail.local.php`.

   Para Gmail, el ejemplo ya trae el host, puerto, usuario y remitente. En
   `password`, coloca una contraseña de aplicación nueva, sin espacios. La que
   se compartió en una conversación debe revocarse primero.

   Las variables de entorno disponibles son:

   - `SMTP_HOST`: servidor SMTP.
   - `SMTP_PORT`: normalmente `587` para TLS o `465` para SSL.
   - `SMTP_USERNAME` y `SMTP_PASSWORD`: credenciales SMTP.
   - `SMTP_ENCRYPTION`: `tls` o `ssl`.
   - `MAIL_FROM_ADDRESS` y `MAIL_FROM_NAME`: remitente mostrado.

La biblioteca PHPMailer ya está incluida con Composer en `vendor/`. En cPanel,
sube `mail.local.php` mediante el Administrador de archivos; no lo publiques
en un repositorio. Luego crea un ticket y consulta el campo `estado` de la
tabla `notificacion` (`enviado` o `fallido`).
