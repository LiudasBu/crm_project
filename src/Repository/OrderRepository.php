<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function setUpAmounts(Order $order)
    {
        $conn = $this->getEntityManager()->getConnection();
        foreach($order->getProducts() as $product)
        {
            $sql = "INSERT INTO order_amounts (order_id, product_id) VALUES (:order_id, :product_id);";
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery(array('order_id' => $order->getId(), 'product_id' => $product->getId()));
        }
    }

    public function getAmounts(Order $order) : array
    {
        $conn = $this->getEntityManager()->getConnection();
        $returnArr = [];
        foreach($order->getProducts() as $product)
        {
            $sql = "SELECT amount FROM order_amounts WHERE order_id = :order_id AND product_id = :product_id;";
            $stmt = $conn->prepare($sql);
            $result = $stmt->executeQuery(array('order_id' => $order->getId(), 'product_id' => $product->getId()));
            $returnArr[$product->getId()] = $result->fetch()['amount'];
        }
        return $returnArr;
    }

    public function updateAmount(Order $order, array $amounts)
    {
        $conn = $this->getEntityManager()->getConnection();
        foreach($amounts as $productId => $amount)
        {
            $sql = "UPDATE order_amounts SET amount = :amount WHERE order_id = :order_id AND product_id = :product_id;";
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery(array('order_id' => $order->getId(), 'product_id' => $productId, 'amount' => $amount));
        }
    }

    // /**
    //  * @return Order[] Returns an array of Order objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getCount(): int
    {
        return $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
