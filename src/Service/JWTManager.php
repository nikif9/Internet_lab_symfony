<?php
/**
 * Сервис для создания и декодирования JWT токенов.
 *
 * @package App\Service
 */
namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTManager
{
    /**
     * Секретный ключ для шифрования JWT.
     *
     * @var string
     */
    private string $secretKey;

    /**
     * Алгоритм шифрования JWT.
     *
     * @var string
     */
    private string $algorithm;

    /**
     * Время жизни токена в секундах.
     *
     * @var int
     */
    private int $expirationTime;

     /**
     * Конструктор JWTManager.
     *
     * @param string $secretKey Секретный ключ.
     * @param string $algorithm Алгоритм шифрования (по умолчанию: HS256).
     * @param int $expirationTime Время жизни токена в секундах (по умолчанию: 3600).
     */
    public function __construct(string $secretKey, string $algorithm = 'HS256', int $expirationTime = 3600)
    {
        $this->secretKey      = $secretKey;
        $this->algorithm      = $algorithm;
        $this->expirationTime = $expirationTime;
    }

    /**
     * Создает JWT токен с заданной полезной нагрузкой.
     *
     * @param array $payload Полезная нагрузка.
     * @return string Сгенерированный JWT токен.
     */
    public function createToken(array $payload): string
    {
        $issuedAt = time();
        $expire   = $issuedAt + $this->expirationTime;
        $data     = [
            'iat'  => $issuedAt,
            'exp'  => $expire,
            'data' => $payload,
        ];

        return JWT::encode($data, $this->secretKey, $this->algorithm);
    }

    /**
     * Декодирует JWT токен.
     *
     * @param string $token JWT токен.
     * @return array|null Декодированная полезная нагрузка или null в случае ошибки.
     */
    public function decodeToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array)$decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }
}
