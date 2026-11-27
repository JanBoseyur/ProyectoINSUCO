<?php

use PHPMailer\PHPMailer\PHPMailer;

class NotificacionService
{
    public function __construct(private PDO $pdo, private array $config)
    {
    }

    /** Guarda el intento y envía el correo. Los fallos quedan auditados. */
    public function enviar(
        int $idUsuario,
        ?int $idTicket,
        string $tipo,
        string $destinatario,
        string $asunto,
        string $mensaje
    ): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO notificacion
                (id_usuario, id_ticket, tipo, destinatario, asunto, mensaje, estado)
             VALUES (:id_usuario, :id_ticket, :tipo, :destinatario, :asunto, :mensaje, 'pendiente')"
        );
        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':id_ticket' => $idTicket,
            ':tipo' => $tipo,
            ':destinatario' => $destinatario,
            ':asunto' => $asunto,
            ':mensaje' => $mensaje
        ]);
        $idNotificacion = (int) $this->pdo->lastInsertId();

        try {
            $directorioPhpMailer = __DIR__ . '/../../vendor/phpmailer/phpmailer/src';
            $archivosPhpMailer = [
                $directorioPhpMailer . '/Exception.php',
                $directorioPhpMailer . '/SMTP.php',
                $directorioPhpMailer . '/PHPMailer.php'
            ];
            foreach ($archivosPhpMailer as $archivoPhpMailer) {
                if (!is_file($archivoPhpMailer)) {
                    throw new RuntimeException('Falta un archivo de PHPMailer en vendor/phpmailer/phpmailer/src. Sube la carpeta vendor completa.');
                }
                require_once $archivoPhpMailer;
            }

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->Port = (int) $this->config['port'];
            $mail->SMTPSecure = strtolower($this->config['encryption']) === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($destinatario);
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;
            $mail->AltBody = trim(strip_tags(str_ireplace(['<br>', '<br/>', '<br />', '</p>'], "\n", $mensaje)));
            $mail->send();

            $stmt = $this->pdo->prepare(
                "UPDATE notificacion SET estado = 'enviado', fecha_envio = CURRENT_TIMESTAMP,
                 error_envio = NULL WHERE id = :id"
            );
            $stmt->execute([':id' => $idNotificacion]);
        } catch (Throwable $e) {
            $stmt = $this->pdo->prepare(
                "UPDATE notificacion SET estado = 'fallido', error_envio = :error WHERE id = :id"
            );
            $stmt->execute([
                ':error' => mb_substr($e->getMessage(), 0, 1000),
                ':id' => $idNotificacion
            ]);
            error_log('No se pudo enviar la notificación por correo: ' . $e->getMessage());
        }

        return $idNotificacion;
    }

    public static function configuracionDesdeEntorno(): array
    {
        return require __DIR__ . '/../../config/mail.php';
    }
}
