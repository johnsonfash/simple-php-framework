<?php

namespace database\drivers;

use core\utils;
use Exception;

class mysql
{
  protected $_connector;
  protected $_from;
  protected $_fields;
  protected $_limit;
  protected $_offset;
  protected $_distinct = '';
  protected $_operator = '';
  protected $_order;
  protected $_update = array();
  protected $_insert = array();
  protected $_direction;
  protected $_join = array();
  protected $_wild = '';
  protected $_where = array();

  /**
   * __construct
   *
   * @param  class $class = instance of db
   * @return void
   */
  public function __construct($class = null)
  {
    $this->_connector = $class;
  }

  /**
   * __quote
   *
   * @param  mixed $val = data to be escaped
   * @return string = mysql stringified value
   */
  protected function __quote($val)
  {
    if (is_string($val)) {
      $escaped = $this->_connector->escape($val);
      return trim($escaped);
    }
    if (is_array($val)) {
      $buffer = [];
      foreach ($val as $i) {
        array_push($buffer, $this->__quote($i));
      }
      $buffer = join(", ", $buffer);
      return "($buffer)";
    }
    if (is_null($val)) {
      return "NULL";
    }
    if (is_bool($val) || is_int($val)) {
      return (int) $val;
    }
    return $this->_connector->escape($val);
  }


  /**
   * from
   *
   * @param  string $from = database table
   * @param  mixed $fields = i.e ['email','name'] | 'name, email' 
   * @return instanceof = self
   */
  public function table(string $from, array $fields = array("*"))
  {
    try {
      if (empty($from)) {
        throw new Exception("Invalid argument");
      }
    } catch (Exception $e) {
      return array("error" => true, "errorMessage" => $e->getMessage());
    }
    $this->_from = $from;
    if ($fields) {
      $this->_fields[$from] = $fields;
    }
    return $this;
  }


  // todo  
  /**
   * perform JOIN query
   *
   * @param  string $join = table1
   * @param  string $on = table2
   * @param  mixed $fields = 
   * @param  string $side = 'INNER' | 'LEFT' | 'RIGHT'
   * @return instance = self
   */
  public function join($join, $on, $fields = [], $side = '')
  {
    try {
      if (empty($join)) {
        throw new Exception("Invalid argument");
      }
      if (empty($on)) {
        throw new Exception("Invalid argument");
      }
    } catch (Exception $e) {
      return array("error" => true, "errorMessage" => $e->getMessage());
    }

    $this->_fields += [$join => $fields];
    $this->_join[] = strtoupper($side) . " JOIN {$join} ON {$on}";
    return $this;
  }

  public function limit(int $limit, $page = 1)
  {
    try {
      if (empty($limit)) {
        throw new Exception("Invalid argument");
      }
    } catch (Exception $e) {
      return array("error" => true, "errorMessage" => $e->getMessage());
    }
    $this->_limit = $limit;
    $this->_offset = $limit * ($page - 1);
    return $this;
  }

  public function orderBy($order, $direction = "ASC")
  {
    try {
      if (empty($order)) throw new Exception("Invalid argument");
    } catch (Exception $e) {
      return array("error" => true, "errorMessage" => $e->getMessage());
    }

    $this->_order = $order;
    $this->_direction = $direction;

    return $this;
  }



  public function where()
  {
    $arguments = func_get_args();
    $pointers = [];
    $values = [];
    if (is_array($arguments[0])) {
      foreach ($arguments[0] as $key => $val) {
        if (strpos($val, '%') !== false || !empty($this->_wild)) {
          $pointers[] = $key . " LIKE ? ";
          if (strpos($val, '%') !== false) {
            $values[] = $val;
          } else {
            $values[] = $this->_wild == 'START' ? $val . '%' : '%' . $val;
          }
        } else {
          $pointers[] = $key . " = ? ";
          $values[] = $val;
        }
      }
      $this->_where = array($pointers, $values);
    } else {
      foreach ($arguments as $key => $val) {
        if (($key % 2) == 0) {
          if (strpos($arguments[$key], 'IN') !== false) {
            $in = $arguments[$key + 1];
            if (!is_array($in)) {
              $in = explode(',', $in);
            }
            foreach ($in as $k => $v) {
              $in[$k] = "?";
            }
            $in = implode(',', $in);
            $pointers[] = $val . " ($in)";
          } else if (strpos($arguments[$key + 1], '%') !== false) {
            $pointers[] = $val . " LIKE ? ";
          } else if (strpos($arguments[$key], '=') !== false) {
            $pointers[] = $val . " ? ";
          } else {
            $pointers[] = $val . " = ? ";
          }
        } else {
          if (strpos($arguments[$key - 1], 'IN') !== false) {
            if (!is_array($val)) {
              $in = explode(',', $val);
            } else {
              $in = $val;
            }
            foreach ($in as $v) {
              $values[] = $v;
            }
          } else {
            $values[] = $val;
          }
        }
      }
    }
    $this->_where = array($pointers, $values);
    return $this;
  }

  public function distinct()
  {
    $this->_distinct = "DISTINCT";
    return $this;
  }

  public function wildCard(string $type = 'START' | 'END')
  {
    $this->_wild = !empty($type) ? $type : 'START';
    return $this;
  }

  protected function _buildSelect()
  {
    $fields = array();
    $where = $order = $limit = $join = "";
    $template = "SELECT " . $this->_distinct . " %s FROM %s %s %s %s %s";
    foreach ($this->_fields as $_fields) {
      foreach ($_fields as $field => $alias) {
        if (is_string($field)) {
          $fields[] = "{$field} AS {$alias}";
        } else {
          $fields[] = $alias;
        }
      }
    }

    $fields = join(", ", $fields);
    $_join = $this->_join;

    if (!empty($_join)) {
      $join = join(" ", $_join);
    }
    if (!empty($this->_where[0])) {
      $_where = $this->_where[0];
      $condition = empty($this->_operator) ? " AND " : $this->_operator;

      $joined = join($condition, $_where);
      $where = "WHERE {$joined}";
    }
    $_order = $this->_order;
    if (!empty($_order)) {
      $_direction = $this->_direction;
      $order = "ORDER BY {$_order} {$_direction}";
    }
    $_limit = $this->_limit;
    if (!empty($_limit)) {
      $_offset = $this->_offset;
      if ($_offset) {
        $limit = "LIMIT {$_limit} OFFSET {$_offset}";
      } else {
        $limit = "LIMIT {$_limit}";
      }
    }
    return sprintf($template, $fields, $this->_from, $join, $where, $order, $limit);
  }

  protected function _buildUpdate($data)
  {
    $parts = array();
    $where = $limit = "";
    $template = "UPDATE %s SET %s %s %s";
    foreach ($data as $field => $val) {
      if (strpos($field, "?") !== false) {
        $parts[] = $field;
      } else if (strpos($field, "+") !== false) {
        $f_break = explode('+', $field);
        $parts[] = "$f_break[0] = $f_break[0] + ?";
      } else if (strpos($field, "-") !== false) {
        $f_break = explode('-', $field);
        $parts[] = "$f_break[0] = $f_break[0] - ?";
      } else {
        $parts[] = "$field = ?";;
      }
      array_push($this->_update, $this->__quote($val));
    }
    $parts = join(", ", $parts);

    // $_where = $this->_where;
    // if (!empty($_where)) {
    //   $joined = join(", ", $_where[0]);
    //   $where = "WHERE {$joined}";
    // }
    if (!empty($this->_where[0])) {
      $_where = $this->_where[0];
      $condition = empty($this->_operator) ? " AND " : $this->_operator;

      $joined = join($condition, $_where);
      $where = "WHERE {$joined}";
    }

    $_limit = $this->_limit;
    if (!empty($_limit)) {
      $_offset = $this->offset;
      $limit = "LIMIT {$_limit} {$_offset}";
    }
    return sprintf($template, $this->_from, $parts, $where, $limit);
  }


  protected function _buildDelete()
  {
    $where = $limit = "";
    $template = "DELETE FROM %s %s %s";
    $_where = $this->_where;
    $condition = empty($this->_operator) ? " AND " : $this->_operator;
    $where = "WHERE " . join($condition, $_where[0]);
    $_limit = $this->_limit;
    if (!empty($_limit)) {
      $_offset = $this->_offset;
      $limit = "LIMIT {$_limit} {$_offset}";
    }
    return sprintf($template, $this->_from, $where, $limit);
  }

  protected function insertOrUpdate($sql)
  {
    $bind =  array_merge($this->_update, $this->_insert, !empty($this->_where) ? $this->_where[1] : array());
    $result = $this->_connector->execute($sql, $bind);
    try {
      if ($result === false) {
        $error = $this->_connector->error();
        if ($error) {
          throw new Exception($error);
        } else {
          return array("error" => false);
        }
      } else {
        return array("error" => false, "data" => $result);
      }
    } catch (Exception $e) {
      return array("error" => true, "errorMessage" => $e->getMessage());
    }
  }

  protected function _buildInsert($data)
  {
    $fields = array();
    $mainValues = array();
    $values1 = array();
    $template = "INSERT INTO %s (%s) VALUES %s";
    foreach ($data as $field => $value1) {
      $values2 = array();
      $secndField = array();
      if (is_array($value1)) {
        $numofValue1 = count($value1);
        foreach ($value1 as $field2 => $value2) {
          if (count($secndField) <= $numofValue1) $secndField[] = $field2;
          $values2[] = "? ";
          array_push($this->_insert, $this->__quote($value2));
        }
        $values2 = "(" . implode(" , ", $values2) . ")";
        array_push($mainValues, $values2);
      }
      if (empty($secndField)) {
        $fields[] = $field;
        array_push($this->_insert, $this->__quote($value1));
      } else {
        $fields = $secndField;
      }
      $values1[] = "?";
    }
    $values1 = "(" . implode(" , ", $values1) . ")";
    if (empty($mainValues)) array_push($mainValues, $values1);
    foreach ($fields as $key => $value) {
      $fields[$key] = "`$value`";
    }
    $fields = join(", ", $fields);
    $mainValues = join(", ", $mainValues);
    return sprintf($template, $this->_from, $fields, $mainValues);
  }


  public function insert($data)
  {
    $sql = $this->_buildInsert($data);
    return $this->insertOrUpdate($sql);
  }


  public function update($data)
  {
    $sql = $this->_buildUpdate($data);
    return $this->insertOrUpdate($sql);
  }

  public function condition($data)
  {
    $this->_operator = " " . strtoupper($data) . " ";
    return $this;
  }

  public function delete()
  {
    $sql = $this->_buildDelete();
    $this->_connector->execute($sql, @$this->_where[1]);
    return $this->_connector->getAffectedRow();
  }


  public function count(string $data = "*")
  {
    $this->_fields = array($this->_from => array("COUNT($data)"));
    $this->limit(1);
    $sql = $this->_buildSelect();
    $result = $this->_connector->execute($sql, @$this->_where[1]);
    $result = $result->fetch_array(MYSQLI_ASSOC)["COUNT($data)"];
    return (int)$result;
  }

  public function first()
  {
    $limit = $this->_limit;
    $offset = $this->_offset;
    $this->limit(1);
    $all = $this->getAll();
    $first = utils::first($all);
    if ($limit) {
      $this->_limit = $limit;
    }
    if ($offset) {
      $this->_offset = $offset;
    }
    return $first;
  }

  public function getAll()
  {
    $sql = $this->_buildSelect();
    // return $sql;
    $result = $this->_connector->execute($sql, @$this->_where[1]);
    try {
      if ($result === false) {
        $error = $this->_connector->error();
        throw new Exception("There was an error with your SQL query: {$error}");
      }
    } catch (Exception $e) {
      return  $e->getMessage();
    }
    $rows = array();
    for ($i = 0; $i < $result->num_rows; $i++) {
      $rows[] = $result->fetch_array(MYSQLI_ASSOC);
    }
    return $rows;
  }
}
