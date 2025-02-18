<?php
/**
 * Репозиторий для сущности User.
 *
 * @package App\Repository
 */
namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    /**
     * Конструктор репозитория.
     *
     * @param ManagerRegistry $registry Менеджер реестра.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
}
