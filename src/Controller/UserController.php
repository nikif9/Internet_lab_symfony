<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\JWTManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/users')]
class UserController extends AbstractController
{
    /**
     * Регистрирует нового пользователя.
     *
     * @param Request $request HTTP-запрос.
     * @param EntityManagerInterface $em Менеджер сущностей.
     * @param UserPasswordHasherInterface $passwordHasher Сервис хэширования паролей.
     * @return JsonResponse JSON-ответ.
     */
    #[Route('', name: 'user_create', methods: ['POST'])]
    public function createUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['username'], $data['password'], $data['email'])) {
            return new JsonResponse(['error' => 'Отсутствуют обязательные поля'], 400);
        }

        // Проверяем, существует ли уже пользователь
        $existingUser = $em->getRepository(User::class)->findOneBy(['username' => $data['username']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Пользователь уже существует'], 400);
        }

        $user = new User($data['username'], '', $data['email']);
        $encodedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($encodedPassword);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'Пользователь создан', 'id' => $user->getId()], 201);
    }

    /**
     * Получает данные пользователя по его ID.
     *
     * @param int $id Идентификатор пользователя.
     * @param UserRepository $userRepository Репозиторий пользователей.
     * @return JsonResponse JSON-ответ с данными пользователя.
     */
    #[Route('/{id}', name: 'user_get', methods: ['GET'])]
    public function getUserById(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Пользователь не найден'], 404);
        }

        return new JsonResponse([
            'id'         => $user->getId(),
            'username'   => $user->getUsername(),
            'email'      => $user->getEmail(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Обновляет данные пользователя.
     *
     * @param int $id Идентификатор пользователя.
     * @param Request $request HTTP-запрос.
     * @param UserRepository $userRepository Репозиторий пользователей.
     * @param EntityManagerInterface $em Менеджер сущностей.
     * @param UserPasswordHasherInterface $passwordHasher Сервис хэширования паролей.
     * @param JWTManager $jwtManager Сервис для работы с JWT.
     * @return JsonResponse JSON-ответ.
     */
    #[Route('/{id}', name: 'user_update', methods: ['PUT'])]
    public function updateUser(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        JWTManager $jwtManager
    ): JsonResponse {
        // Извлекаем токен из заголовка Authorization
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }
        $token = $matches[1];
        $payload = $jwtManager->decodeToken($token);
        if (!$payload || $payload['user_id'] != $id) {
            return new JsonResponse(['error' => 'Forbidden'], 403);
        }

        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Пользователь не найден'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $encodedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($encodedPassword);
        }
        $em->flush();

        return new JsonResponse(['message' => 'Пользователь обновлён']);
    }

    /**
     * Удаляет пользователя.
     *
     * @param int $id Идентификатор пользователя.
     * @param Request $request HTTP-запрос.
     * @param UserRepository $userRepository Репозиторий пользователей.
     * @param EntityManagerInterface $em Менеджер сущностей.
     * @param JWTManager $jwtManager Сервис для работы с JWT.
     * @return JsonResponse JSON-ответ.
     */
    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function deleteUser(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        JWTManager $jwtManager
    ): JsonResponse {
        // Извлекаем токен из заголовка Authorization
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }
        $token = $matches[1];
        $payload = $jwtManager->decodeToken($token);
        if (!$payload || $payload['user_id'] != $id) {
            return new JsonResponse(['error' => 'Forbidden'], 403);
        }

        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['error' => 'Пользователь не найден'], 404);
        }
        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'Пользователь удалён']);
    }
}
