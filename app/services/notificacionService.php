
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificacionService
{
    private PDO $pdo;
    private array $config;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    /**
     * Registra y envía una notificación por correo.
     */
    public function enviar(
        int $idUsuario,
        ?int $idTicket,
        string $tipo,
        string $destinatario,
        string $asunto,
        string $mensaje
    ): int {

        // 1. Registrar notificación como pendiente

        $sql = "INSERT INTO notificacion (
                    id_usuario,
                    id_ticket,
                    tipo,
                    destinatario,
                    asunto,
                    mensaje,
                    estado
                ) VALUES (
                    :id_usuario,
                    :id_ticket,
                    :tipo,
                    :destinatario,
                    :asunto,
                    :mensaje,
                    'pendiente'
                )";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':id_ticket' => $idTicket,
            ':tipo' => $tipo,
            ':destinatario' => $destinatario,
            ':asunto' => $asunto,
            ':mensaje' => $mensaje
        ]);

        $idNotificacion = (int) $this->pdo->lastInsertId();


        // 2. Intentar enviar el correo

        try {

            $mail = new PHPMailer(true);

            // Configuración SMTP

            $mail->isSMTP();

            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;

            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];

            $mail->Port = $this->config['port'];

            $mail->SMTPSecure =
                $this->config['encryption'] === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;

            // Remitente

            $mail->CharSet = 'UTF-8';

            $mail->setFrom(
                $this->config['from_email'],
                $this->config['from_name']
            );

            // Destinatario

            $mail->addAddress($destinatario);

            // Contenido

            $mail->isHTML(true);

            $mail->Subject = $asunto;
            $mail->Body = $mensaje;

            $mail->AltBody = trim(
                strip_tags(
                    str_replace(
                        ['<br>', '<br/>', '<br />', '</p>'],
                        "\n",
                        $mensaje
                    )
                )
            );

            // Enviar

            $mail->send();


            // 3. Actualizar estado a enviado

            $sql = "UPDATE notificacion
                    SET estado = 'enviado',
                        fecha_envio = CURRENT_TIMESTAMP,
                        error_envio = NULL
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':id' => $idNotificacion
            ]);

        } catch (\Throwable $e) {

            // 4. Registrar error de envío

            $sql = "UPDATE notificacion
                    SET estado = 'fallido',
                        error_envio = :error
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                ':error' => $e->getMessage(),
                ':id' => $idNotificacion
            ]);
        }

        return $idNotificacion;
    }
}
