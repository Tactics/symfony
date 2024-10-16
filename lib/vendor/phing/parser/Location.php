<?php
/*
 *  $Id: Location.php 3076 2006-12-18 08:52:12Z fabien $
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
 * Stores the file name and line number of a XML file
 *
 * @author      Andreas Aderhold <andi@binarycloud.com>
 * @copyright � 2001,2002 THYRELL. All rights reserved
 * @version   $Revision: 1.6 $ $Date: 2003/12/24 13:02:09 $
 * @access    public
 * @package   phing.parser
 */

class Location {

    /**
     * Constructs the location consisting of a file name and line number
     *
     * @param  string  the filename
     * @param  integer the line number
     * @param  integer the column number
     * @access public
     */
    function __construct(private $fileName = null, private $lineNumber = null, private $columnNumber = null)
    {
    }

    /**
     * Returns the file name, line number and a trailing space.
     *
     * An error message can be appended easily. For unknown locations,
     * returns empty string.
     *
     * @return string the string representation of this Location object
     * @access public
     */
    function toString() {
        $buf = "";
        if ($this->fileName !== null) {
            $buf.=$this->fileName;
            if ($this->lineNumber !== null) {
                $buf.= ":".$this->lineNumber;
            }
            $buf.=":".$this->columnNumber;
        }
        return (string) $buf;
    }
}
