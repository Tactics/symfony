<?php
/*
 *  $Id: TstampTask.php 3076 2006-12-18 08:52:12Z fabien $
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
 * <http://phing.info>.
 */

/**
 * Sets properties to the current time, or offsets from the current time.
 * The default properties are TSTAMP, DSTAMP and TODAY;
 *
 * Based on Ant's Tstamp task.
 *
 * @author   Michiel Rook <michiel.rook@gmail.com>
 * @version  $Revision: 1.6 $
 * @package  phing.tasks.system
 * @since    2.2.0
 */
class TstampTask extends Task
{
	private $customFormats = [];

	private $prefix = "";

	/**
	 * Set a prefix for the properties. If the prefix does not end with a "."
	 * one is automatically added.
	 * @param prefix the prefix to use.
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;

		if (!empty($this->prefix))
		{
			$this->prefix.= ".";
		}
	}

    /**
     * Create the timestamps. Custom ones are done before
     * the standard ones.
     *
     * @throws BuildException
     */
    public function main()
    {
		foreach ($this->customFormats as $cf)
		{
			$cf->execute($this);
		}

		$dstamp = date('Y-m-d', time());
		$this->prefixProperty('DSTAMP', $dstamp);

		$tstamp =  date('H:i', time());
		$this->prefixProperty('TSTAMP', $tstamp);

		$today = date('F d Y', time());
		$this->prefixProperty('TODAY', $today);
	}

    /**
     * helper that encapsulates prefix logic and property setting
     * policy (i.e. we use setNewProperty instead of setProperty).
     */
    public function prefixProperty($name, $value)
    {
        $this->getProject()->setNewProperty($this->prefix . $name, $value);
    }
}

?>
