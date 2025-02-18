<?php
/**
 * Контроллер для аутентификации пользователей.
 *
 * @package App\Controller
 */
namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\JWTManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    /**
     * Обрабатывает вход в систему (логин).
     *
     * @Route("/login", name="login", methods={"POST"})
     *
     * @param Request $request HTTP-запрос.
     * @param UserRepository $userRepository Репозиторий пользователей.
     * @param JWTManager $jwtManager Сервис для работы с JWT.
     * @param UserPasswordHasherInterface $passwordHasher Сервис для хэширования паролей.
     * @return JsonResponse JSON-ответ с JWT токеном.
     */
    public function login(
        Request $request,
        UserRepository $userRepository,
        JWTManager $jwtManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username'], $data['password'])) {
            return new JsonResponse(['error' => 'Отсутствуют поля username или password'], 400);
        }

        $user = $userRepository->findOneBy(['username' => $data['username']]);
        if (!$user) {
            return new JsonResponse(['error' => 'Неверные учётные данные'], 401);
        }

        // Проверка пароля (предполагается, что пароль закодирован)
        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Неверные учётные данные'], 401);
        }

        // Генерация JWT-токена (payload содержит идентификатор пользователя)
        $token = $jwtManager->createToken(['user_id' => $user->getId()]);

        return new JsonResponse([
            'message' => 'Авторизация успешна',
            'user_id' => $user->getId(),
            'token'   => $token,
        ]);
    }
}
