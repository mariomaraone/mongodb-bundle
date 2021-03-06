<?php declare(strict_types = 1);

namespace Facile\MongoDbBundle\Capsule;

use Facile\MongoDbBundle\Event\QueryEvent;
use Facile\MongoDbBundle\Models\Query;
use MongoDB\Collection as MongoCollection;
use MongoDB\Driver\Manager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Collection.
 * @internal
 */
final class Collection extends MongoCollection
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * Collection constructor.
     *
     * @param Manager                  $manager
     * @param string                   $databaseName
     * @param string                   $collectionName
     * @param array                    $options
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @internal param DataCollectorLoggerInterface $logger
     */
    public function __construct(Manager $manager, $databaseName, $collectionName, array $options = [], EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($manager, $databaseName, $collectionName, $options);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function aggregate(array $pipeline, array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, null, $pipeline, $options);
        $result = parent::aggregate($query->getData(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function count($filter = [], array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, $filter, null, $options);
        $result = parent::count($query->getFilters(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function find($filter = [], array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, $filter, null, $options);
        $result = parent::find($query->getFilters(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findOne($filter = [], array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, $filter, null, $options);
        $result = parent::findOne($query->getFilters(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneAndUpdate($filter, $update, array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, $filter, $update, $options);
        $result = parent::findOneAndUpdate($query->getFilters(), $query->getData(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneAndDelete($filter, array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, $filter, null, $options);
        $result = parent::findOneAndDelete($query->getFilters(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMany($filter, array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, $filter, null, $options);
        $result = parent::deleteMany($query->getFilters(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteOne($filter, array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, $filter, null, $options);
        $result = parent::deleteOne($query->getFilters(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function replaceOne($filter, $replacement, array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, $filter, $replacement, $options);
        $result = parent::replaceOne($query->getFilters(), $query->getData(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function insertOne($document, array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, [], $document, $options);
        $result = parent::insertOne($query->getData(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function updateOne($filter, $update, array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, $filter, $update, $options);
        $result = parent::updateOne($query->getFilters(), $query->getData(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function distinct($fieldName, $filter = [], array $options = [])
    {
        $query = $this->prepareQuery(__FUNCTION__, $filter, ['fieldName' => $fieldName], $options);
        $result = parent::distinct($fieldName, $query->getFilters(), $query->getOptions());
        $this->notifyQueryExecution($query);

        return $result;
    }

    /**
     * @param string $method
     * @param array|object  $filters
     * @param array|object  $data
     * @param array  $options
     *
     * @return Query
     */
    private function prepareQuery(string $method, $filters = null, $data = null, array $options): Query
    {
        $query = new Query();
        $query->setFilters($filters);
        $query->setData($data);
        $query->setOptions($options);
        $query->setMethod($method);
        $query->setCollection($this->getCollectionName());

        $this->eventDispatcher->dispatch(QueryEvent::QUERY_PREPARED, new QueryEvent($query));

        return $query;
    }

    /**
     * @param Query $queryLog
     *
     * @return Query
     */
    private function notifyQueryExecution(Query $queryLog)
    {
        $queryLog->setExecutionTime(microtime(true) - $queryLog->getStart());

        $this->eventDispatcher->dispatch(QueryEvent::QUERY_EXECUTED, new QueryEvent($queryLog));
    }
}

