<?php declare(strict_types=1);

namespace App\Repository;

use App\Entity\Coupon;
use App\Service\PriceBuilder\CouponProviderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * @extends ServiceEntityRepository<Coupon>
 *
 * @method Coupon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Coupon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Coupon[]    findAll()
 * @method Coupon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
#[AsAlias(CouponProviderInterface::class)]
class CouponRepository extends ServiceEntityRepository implements CouponProviderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coupon::class);
    }

    public function getCoupon(string $code): Coupon
    {
        return $this->findOneBy(['code' => $code]) ?? throw new NoResultException();
    }
}
