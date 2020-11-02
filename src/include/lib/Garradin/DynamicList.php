<?php

namespace Garradin;

class DynamicList
{
	protected $columns;
	protected $tables;
	protected $conditions;
	protected $group;
	protected $order;
	protected $count = 'COUNT(*)';
	protected $desc = true;
	protected $per_page = 100;
	protected $page = 1;

	private $count_result;

	public function __construct(array $columns, string $tables, string $conditions)
	{
		$this->columns = $columns;
		$this->tables = $tables;
		$this->conditions = $conditions;
		$this->order = key($columns);
	}

	public function __get($key)
	{
		return $this->$key;
	}

	public function setConditions(string $conditions)
	{
		$this->conditions = $conditions;
	}

	public function orderBy(string $key, bool $desc)
	{
		if (!array_key_exists($key, $this->columns)) {
			throw new UserException('Invalid order: ' . $key);
		}

		$this->order = $key;
		$this->desc = $desc;
	}

	public function groupBy(string $value)
	{
		$this->group = $value;
	}

	public function count()
	{
		if (null === $this->count_result) {
			$sql = sprintf('SELECT %s FROM %s WHERE %s;', $this->count, $this->tables, $this->conditions);
			$this->count_result = DB::getInstance()->firstColumn($sql);
		}

		return $this->count_result;
	}

	public function paginationURL()
	{
		$query = array_merge($_GET, ['p' => '[ID]']);
		$url = Utils::getSelfURL($query);
		return $url;
	}

	public function orderURL(string $order, bool $desc)
	{
		$query = array_merge($_GET, ['o' => $order, 'd' => (int) $desc]);
		$url = Utils::getSelfURL($query);
		return $url;
	}

	public function setCount(string $count)
	{
		$this->count = $count;
	}

	public function iterate()
	{
		$start = ($this->page - 1) * $this->per_page;
		$columns = [];

		foreach ($this->columns as $alias => $properties) {
			$select = isset($properties['select']) ? $properties['select'] : $alias;
			$columns[] = sprintf('%s AS %s', $select, $alias);
		}

		$columns = implode(', ', $columns);

		$order = isset($this->columns[$this->order]['order']) ? $this->columns[$this->order]['order'] : $this->order;

		if ($this->desc) {
			$order .= ' DESC';
		}

		$group = $this->group ? 'GROUP BY ' . $this->group : '';

		$sql = sprintf('SELECT %s FROM %s WHERE %s %s ORDER BY %s LIMIT %d,%d;',
			$columns, $this->tables, $this->conditions, $group, $order, $start, $this->per_page);

		return DB::getInstance()->iterate($sql);
	}

	public function loadFromQueryString()
	{
		if (!empty($_GET['o'])) {
			$this->orderBy($_GET['o'], !empty($_GET['d']));
		}

		if (!empty($_GET['p'])) {
			$this->page = (int)$_GET['p'];
		}
	}
}