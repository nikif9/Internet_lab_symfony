<?php
/**
 * Класс-сущность пользователя.
 *
 * @package App\Entity
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: "App\Repository\UserRepository")]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Уникальный идентификатор пользователя.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    /**
     * Имя пользователя (уникальное).
     *
     * @var string
     */
    #[ORM\Column(type: "string", unique: true)]
    private string $username;

     /**
     * Хэшированный пароль.
     *
     * @var string
     */
    #[ORM\Column(type: "string")]
    private string $password;
    
    /**
     * Адрес электронной почты.
     *
     * @var string
     */
    #[ORM\Column(type: "string")]
    private string $email;

    /**
     * Дата и время создания записи.
     *
     * @var \DateTimeInterface
     */
    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    /**
     * Конструктор.
     *
     * @param string $username Имя пользователя.
     * @param string $password Пароль (в виде строки, который потом будет захэширован).
     * @param string $email Электронная почта.
     */
    public function __construct(string $username, string $password, string $email)
    {
        $this->username  = $username;
        $this->password  = $password;
        $this->email     = $email;
        $this->createdAt = new \DateTime();
    }

    /**
     * Получить уникальный идентификатор пользователя.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Получение имени пользователя.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Установка имени пользователя.
     *
     * @param string $username Имя пользователя.
     *
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Получение хэшированного пароля.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Установка пароля.
     *
     * @param string $password Пароль (в виде строки, который потом будет захэширован).
     *
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Получение адреса электронной почты.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
    
    /**
     * Установка адреса электронной почты.
     *
     * @param string $email Адрес электронной почты.
     *
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
    
    /**
     * Получение даты создания.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    // Методы UserInterface

    /**
     * Возвращает идентификатор пользователя для системы безопасности.
     *
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * Возвращает массив ролей пользователя.
     *
     * @return array
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * Возвращает соль, использованную при хэшировании пароля.
     *
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return null;
    }
    
    /**
     * Удаляет временные или чувствительные данные пользователя.
     */
    public function eraseCredentials(): void
    {
    }
}
