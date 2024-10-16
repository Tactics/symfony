<?php
/*
 *  $Id: MSSQLSRVPreparedStatement.php 506 2009-01-30 16:53:01Z jupeter $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://creole.phpdb.org>.
 */

require_once 'creole/PreparedStatement.php';
require_once 'creole/common/PreparedStatementCommon.php';

/**
 * MSSQL specific PreparedStatement functions.
 *
 * @author    Piotr Plenik <piotr.plenik@teamlab.pl>
 * @version   $Revision: 506 $
 * @package   creole.drivers.mssqlsrv
 */
class MSSQLSRVPreparedStatement extends PreparedStatementCommon implements PreparedStatement {

  /**
   * @inheritdoc
   *
   */
  public function __construct(Connection $conn, $sql)
  {
    if (false !== stripos($sql, 'insert into'))
    {
      $sql .= '; SELECT SCOPE_IDENTITY() AS ID';
    }

    parent::__construct($conn, $sql);
  }

  /**
   * Add quotes using str_replace.
   * This is not as thorough as MySQL.
   */
  protected function escape($subject)
  {
    // use this instead of magic_quotes_sybase + addslashes(),
    // just in case multiple RDBMS being used at the same time
    return str_replace("'", "''", $subject);
  }

  /**
   * MSSQL must emulate OFFSET/LIMIT support.
   */
  public function executeQuery($p1 = null, $fetchmode = null)
  {
    $params = null;
    if ($fetchmode !== null) {
      $params = $p1;
    } elseif ($p1 !== null) {
      if (is_array($p1)) $params = $p1;
      else $fetchmode = $p1;
    }

    if ($params) {
      for($i=0,$cnt=count($params); $i < $cnt; $i++) {
        $this->set($i+1, $params[$i]);
      }
    }

    $this->updateCount = null; // reset
    $sql = $this->replaceParams();

    if(stripos($sql, 'ORDER BY'))
      $offsetBefore = true;
    else
      $offsetBefore = false;

    if($offsetBefore)
    {
      if ($this->limit > 0 || $this->offset > 0) {
        $this->conn->applyLimit($sql, $this->offset, $this->limit);
      }
    }

    $this->resultSet = $this->conn->executeQuery($sql, $fetchmode);

    if(!$offsetBefore)
    {
      $this->resultSet->_setOffset($this->offset);
      $this->resultSet->_setLimit($this->limit);
    }

    return $this->resultSet;
  }

  /**
   * MSSQL-specific implementation of setBlob().
   *
   * If you are having trouble getting BLOB data into the database, see the phpdoc comment
   * in the MSSQLConnection for some PHP ini values that may need to be set. (This also
   * applies to CLOB support.)
   *
   * @param int $paramIndex
   * @param mixed $value Blob object or string.
   * @return void
   */
  function setBlob($paramIndex, $blob)
  {
    $this->sql_cache_valid = false;
    if ($blob === null) {
      $this->setNull($paramIndex);
    } else {
      // they took magic __toString() out of PHP5.0.0; this sucks
      if (is_object($blob)) {
        $blob = $blob->__toString();
      }
      $data = unpack("H*hex", (string) $blob);
      $this->boundInVars[$paramIndex] = '0x'.$data['hex']; // no surrounding quotes!
    }
  }
}
