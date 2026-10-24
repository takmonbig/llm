<?php
declare(strict_types=1);

namespace App\Core;

/** Minimal mailer: drivers = log (dev), mail (PHP mail()), smtp (native SMTP with STARTTLS/SSL + AUTH LOGIN). */
final class Mailer
{
    public static function send(string $to, string $subject, string $html, string $text = ''): bool
    {
        $driver = strtolower((string) env('MAIL_DRIVER', 'log'));
        $fromEmail = (string) env('MAIL_FROM_ADDRESS', 'no-reply@example.com');
        $fromName = (string) env('MAIL_FROM_NAME', setting('store_name', 'Long Live Men'));
        $text = $text ?: trim(strip_tags(preg_replace('/<br\s*\/?>|<\/p>/i', "\n", $html) ?? ''));
        $boundary = 'b' . bin2hex(random_bytes(12));
        $headers = [
            'From: ' . self::encode($fromName) . " <$fromEmail>",
            'Reply-To: ' . (setting('support_email') ?: $fromEmail),
            'MIME-Version: 1.0',
            "Content-Type: multipart/alternative; boundary=\"$boundary\"",
            'X-Mailer: LongLiveMen-PHP',
        ];
        $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n$text\r\n\r\n"
            . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n$html\r\n\r\n--$boundary--\r\n";

        try {
            return match ($driver) {
                'smtp' => self::smtp($fromEmail, $to, $subject, $headers, $body),
                'mail' => mail($to, self::encode($subject), $body, implode("\r\n", $headers)),
                default => self::logMail($to, $subject, $text, $html),
            };
        } catch (\Throwable $e) {
            Logger::error('Mail failed: ' . $e->getMessage(), ['to' => $to, 'subject' => $subject]);
            return false;
        }
    }

    private static function logMail(string $to, string $subject, string $text, string $html): bool
    {
        $entry = sprintf("=== %s ===\nTo: %s\nSubject: %s\n\n%s\n\n", date('c'), $to, $subject, $text);
        return (bool) @file_put_contents(STORAGE_PATH . '/logs/mail.log', $entry, FILE_APPEND | LOCK_EX);
    }

    private static function encode(string $s): string
    {
        return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s;
    }

    private static function smtp(string $from, string $to, string $subject, array $headers, string $body): bool
    {
        $host = (string) env('MAIL_HOST');
        $port = (int) env('MAIL_PORT', 587);
        $enc = strtolower((string) env('MAIL_ENCRYPTION', 'tls'));
        $user = (string) env('MAIL_USERNAME');
        $pass = (string) env('MAIL_PASSWORD');
        $remote = ($enc === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $sock = @stream_socket_client($remote, $errno, $errstr, 15);
        if (!$sock) {
            throw new \RuntimeException("SMTP connect failed: $errstr");
        }
        stream_set_timeout($sock, 15);
        $read = function () use ($sock): string {
            $data = '';
            while ($line = fgets($sock, 515)) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $data;
        };
        $cmd = function (string $c, array $ok) use ($sock, $read): string {
            fwrite($sock, $c . "\r\n");
            $r = $read();
            if (!in_array((int) substr($r, 0, 3), $ok, true)) {
                throw new \RuntimeException("SMTP error after '$c': " . trim($r));
            }
            return $r;
        };
        $read();
        $cmd('EHLO ' . (parse_url(app_url(), PHP_URL_HOST) ?: 'localhost'), [250]);
        if ($enc === 'tls') {
            $cmd('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException('STARTTLS negotiation failed');
            }
            $cmd('EHLO ' . (parse_url(app_url(), PHP_URL_HOST) ?: 'localhost'), [250]);
        }
        if ($user !== '') {
            $cmd('AUTH LOGIN', [334]);
            $cmd(base64_encode($user), [334]);
            $cmd(base64_encode($pass), [235]);
        }
        $cmd("MAIL FROM:<$from>", [250]);
        $cmd("RCPT TO:<$to>", [250, 251]);
        $cmd('DATA', [354]);
        $msg = "To: $to\r\nSubject: " . self::encode($subject) . "\r\nDate: " . date('r') . "\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $body;
        $msg = str_replace("\n.", "\n..", $msg);
        $cmd($msg . "\r\n.", [250]);
        $cmd('QUIT', [221]);
        fclose($sock);
        return true;
    }
}
