<?php
/*
 *  $Id: Persistent.php 536 2007-01-10 14:30:38Z heltem $
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
 * <http://propel.phpdb.org>.
 */

/**
 * This interface defines methods related to saving an object
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @author     John D. McNally <jmcnally@collab.net> (Torque)
 * @author     Fedor K. <fedor@apache.org> (Torque)
 * @version    $Revision: 536 $
 * @package    propel.om
 */
interface Persistent {

	/**
	 * getter for the object primaryKey.
	 */
	public function getPrimaryKey();

	/**
	 * Sets the PrimaryKey for the object.
	 *
	 * @param      mixed $primaryKey The new PrimaryKey object or string (result of PrimaryKey.toString()).
	 * @return     void
	 * @throws     Exception This method might throw an exceptions
	 */
	public function setPrimaryKey(mixed $primaryKey): void;


	/**
	 * Returns whether the object has been modified, since it was
	 * last retrieved from storage.
	 *
	 * @return     boolean True if the object has been modified.
	 */
	public function isModified(): bool;

	/**
	 * Has specified column been modified?
	 *
	 * @param string $col
	 * @return     boolean True if $col has been modified.
	 */
	public function isColumnModified(string $col): bool;

	/**
	 * Returns whether the object has ever been saved.  This will
	 * be false, if the object was retrieved from storage or was created
	 * and then saved.
	 *
	 * @return     boolean True, if the object has never been persisted.
	 */
	public function isNew(): bool;

	/**
	 * Setter for the isNew attribute.  This method will be called
	 * by Propel-generated children and Peers.
	 *
	 * @param bool $b the state of the object.
	 */
	public function setNew(bool $b);

	/**
	 * Resets (to false) the "modified" state for this object.
	 *
	 * @return     void
	 */
	public function resetModified() : void;

	/**
	 * Whether this object has been deleted.
	 * @return     bool The deleted state of this object.
	 */
	public function isDeleted(): bool;

	/**
	 * Specify whether this object has been deleted.
	 * @param bool $b The deleted state of this object.
	 * @return     void
	 */
	public function setDeleted(bool $b) : void;

    /**
     * Deletes the object.
     * @param      Connection|null $connection
     * @return     void
     * @throws     Exception
     */
	public function delete(Connection $connection = null): void;

    /**
     * Saves the object.
     * @param      Connection|null $connection
     * @return     void
     * @throws     Exception
     */
	public function save(Connection $connection = null): int;
}
