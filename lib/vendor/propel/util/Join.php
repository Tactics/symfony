<?php


/**
 * Data object to describe a join between two tables, for example
 * <pre>
 * table_a LEFT JOIN table_b ON table_a.id = table_b.a_id
 * </pre>
 */
class Join
{
    /**
     * Constructor
     * @param      string $leftColumn the left column of the join condition;
     *        might contain an alias name
     * @param      string $rightColumn the right column of the join condition
     *        might contain an alias name
     * @param      string $joinType the type of the join. Valid join types are
     *        null (adding the join condition to the where clause),
     *        Criteria::LEFT_JOIN(), Criteria::RIGHT_JOIN(), and Criteria::INNER_JOIN()
     */
    public function __construct(private $leftColumn, private $rightColumn, private $joinType = null)
    {
    }

    /**
     * @return     string the type of the join, i.e. Criteria::LEFT_JOIN(), ...,
     *         or null for adding the join condition to the where Clause
     */
    public function getJoinType()
    {
        return $this->joinType;
    }

    /**
     * the left column of the join condition
     *
     * @return null|string
     */
    public function getLeftColumn()
    {
        return $this->leftColumn;
    }

    /**
     * @return bool|string
     */
    public function getLeftColumnName()
    {
        return substr($this->leftColumn, strpos($this->leftColumn, '.') + 1);
    }

    /**
     * @return bool|string
     */
    public function getLeftTableName()
    {
        return substr($this->leftColumn, 0, strpos($this->leftColumn, '.'));
    }

    /**
     * @return null|string the right column of the join condition
     */
    public function getRightColumn()
    {
        return $this->rightColumn;
    }

    /**
     * @return bool|string
     */
    public function getRightColumnName()
    {
        return substr($this->rightColumn, strpos($this->rightColumn, '.') + 1);
    }

    /**
     * @return bool|string
     */
    public function getRightTableName()
    {
        return substr($this->rightColumn, 0, strpos($this->rightColumn, '.'));
    }

    /**
     * returns a String representation of the class,
     * mainly for debugging purposes
     * @return     string a String representation of the class
     */
    public function toString()
    {
        $result = "";
        if ($this->joinType != null)
        {
            $result .= $this->joinType . " : ";
        }
        $result .= $this->leftColumn . "=" . $this->rightColumn . " (ignoreCase not considered)";

        return $result;
    }
}
