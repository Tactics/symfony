<?php
/*
 *  $Id: BufferedWriter.php 3076 2006-12-18 08:52:12Z fabien $
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
 
include_once 'phing/system/io/Writer.php';

/**
 * Convenience class for writing files.
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.10 $
 * @package   phing.system.io 
 */
class BufferedWriter extends Writer {
    
    function __construct(
        /**
         * The Writer we are buffering output to.
         */
        private readonly Writer $out,
        /**
         * The size of the buffer in kb.
         */
        private $bufferSize = 8192
    )
    {
    }

    function write($buf, $off = null, $len = null) {
        return $this->out->write($buf, $off, $len);
    }
    
    function newLine() {
        $this->write(Phing::getProperty('line.separator'));
    }
    
    function getResource() {
        return $this->out->getResource();
    }

    function reset() {
        return $this->out->reset();
    }
    
    function close() {
        return $this->out->close();
    }
    
    function open() {
        return $this->out->open();
    }
    
}
