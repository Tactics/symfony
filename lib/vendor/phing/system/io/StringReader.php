<?php
/*
 *  $Id: StringReader.php 3076 2006-12-18 08:52:12Z fabien $
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
 * Dummy class for reading character streams. 
 * @package phing.system.io
 */
class StringReader extends Reader {
    
    private $mark = 0;
    private $currPos = 0;
    
    function __construct(private $_string)
    {
    }

    function skip($n) {}

    function read($len = null) {
        if ($len === null) {
            return $this->_string;
        } else {            
            if ($this->currPos >= strlen((string) $this->_string)) {
                return -1;
            }            
            $out = substr((string) $this->_string, $this->currPos, $len);
            $this->currPos += $len;
            return $out;
        }
    }

    function mark() {
        $this->mark = $this->currPos;
    }

    function reset() {
        $this->currPos = $this->mark;
    }

    function close() {}

    function open() {}

    function ready() {}

    function markSupported() {
        return true;
    }
    
    function getResource() {
        return '(string) "'.$this->_string . '"';
    }
}
?>
