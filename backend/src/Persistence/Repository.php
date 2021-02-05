<?php

namespace TestingTimes\Persistence;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use TestingTimes\Http\Request\Request;

abstract class Repository
{
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @var null
     */
    protected $query = null;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * Repository constructor.
     * @param  EntityManagerInterface  $em
     */
    public function __construct(EntityManagerInterface $em, Request $request)
    {
        $this->em = $em;
        $this->request = $request;
    }

    /**
     * @return ObjectRepository
     * @throws Exception
     */
    public function getRepository()
    {
        return $this->em->getRepository($this->getModelName());
    }

    public function getNewQuery()
    {
        return $this->em->createQueryBuilder();
    }

    public function getQuery()
    {
        if ($this->query === null) {
            $this->query = $this->getNewQuery();
        }

        return $this->query;
    }

    public function reset()
    {
        return $this->query = null;
    }

    public function index(?array $criteria = null, ?int $page = null, ?int $limit = null)
    {
        $page = $page ?? ((int)$this->request->query('page') ? $this->request->query('page') : 1);
        $page = max((int)$page, 1);
        $limit = $limit ?? ((int)$this->request->query('limit') ? $this->request->query('limit') : 20);
        $offset = $page === 1 ? 0 : (($page - 1) * $limit);

        // todo: needed for return:
        // - max count based on criteria
        // - the data : done
        // - the meta

        if ($criteria) {
            $this->applyCriteria($this->getQuery(), $criteria);
        }

        $data = (clone $this->getQuery())->select('T')
            ->from($this->getModelName(), 'T')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $count = (clone $this->getQuery())->select('count(T)')
            ->from($this->getModelName(), 'T')
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return [
            'metadata' => [
                'currentPage' => (int)$page,
                'totalPages' => ceil((int)$count / (int)$limit),
                'totalItems' => (int)$count,
                'perPage' => (int)$limit,
            ],
            'data' => $data
        ];
    }

    private function applyCriteria($query, array $criteria)
    {
        if (is_array($criteria)) {
            $i = 0;
            foreach ($criteria as $criterion) {
                $query->where("T.{$criterion[0]} {$criterion[1]} :v{$i}");
                $query->setParameter("v{$i}", $criterion[2]);
                $i++;
            }
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getModelName(): string
    {
        if (!isset(static::$model)) {
            $className = static::class;
            throw new Exception("{$className}::model property is not set set it in the class.");
        }

        return static::$model;
    }
}
