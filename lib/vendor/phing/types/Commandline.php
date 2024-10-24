<?php
/*
 *  $Id: Commandline.php 3076 2006-12-18 08:52:12Z fabien $
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
 * Commandline objects help handling command lines specifying processes to
 * execute.
 *
 * The class can be used to define a command line as nested elements or as a
 * helper to define a command line by an application.
 * <p>
 * <code>
 * &lt;someelement&gt;<br>
 * &nbsp;&nbsp;&lt;acommandline executable="/executable/to/run"&gt;<br>
 * &nbsp;&nbsp;&nbsp;&nbsp;&lt;argument value="argument 1" /&gt;<br>
 * &nbsp;&nbsp;&nbsp;&nbsp;&lt;argument line="argument_1 argument_2 argument_3" /&gt;<br>
 * &nbsp;&nbsp;&nbsp;&nbsp;&lt;argument value="argument 4" /&gt;<br>
 * &nbsp;&nbsp;&lt;/acommandline&gt;<br>
 * &lt;/someelement&gt;<br>
 * </code>
 * The element <code>someelement</code> must provide a method
 * <code>createAcommandline</code> which returns an instance of this class.
 *
 * @author thomas.haas@softwired-inc.com
 * @author <a href="mailto:stefan.bodewig@epost.de">Stefan Bodewig</a>
 */
class Commandline implements \Stringable {

    /**
     * @var array CommandlineArguments[]
     */
    public $arguments = []; // public so "inner" class can access

    /**
     * Full path (if not on %PATH% env var) to executable program.
     * @var string
     */
    public $executable; // public so "inner" class can access

    const DISCLAIMER = "The ' characters around the executable and arguments are not part of the command.";

    public function __construct($to_process = null) {
        if ($to_process !== null) {
            $tmp = static::translateCommandline($to_process);
            if ($tmp) {
                $this->setExecutable(array_shift($tmp)); // removes first el
                foreach($tmp as $arg) { // iterate through remaining elements
                    $this->createArgument()->setValue($arg);
                }
            }
        }
    }


    /**
     * Creates an argument object and adds it to our list of args.
     *
     * <p>Each commandline object has at most one instance of the
     * argument class.</p>
     *
     * @param boolean $insertAtStart if true, the argument is inserted at the
     * beginning of the list of args, otherwise it is appended.
     * @return CommandlineArgument
     */
    public function createArgument($insertAtStart = false) {
        $argument = new CommandlineArgument($this);
        if ($insertAtStart) {
            array_unshift($this->arguments, $argument);
        } else {
            array_push($this->arguments, $argument);
        }
        return $argument;
    }

    /**
     * Sets the executable to run.
     */
    public function setExecutable($executable) {
        if (!$executable) {
            return;
        }
        $this->executable = $executable;
        $this->executable = strtr($this->executable, '/', DIRECTORY_SEPARATOR);
        $this->executable = strtr($this->executable, '\\', DIRECTORY_SEPARATOR);
    }

    public function getExecutable() {
        return $this->executable;
    }

    public function addArguments($line) {
        foreach($line as $arg) {
            $this->createArgument()->setValue($arg);
        }
    }

    /**
     * Returns the executable and all defined arguments.
     * @return array
     */
    public function getCommandline() {
        $args = $this->getArguments();
        if ($this->executable === null) {
            return $args;
        }
        return array_merge([$this->executable], $args);
    }


    /**
     * Returns all arguments defined by <code>addLine</code>,
     * <code>addValue</code> or the argument object.
     */
    public function getArguments() {
        $result = [];
        foreach($this->arguments as $arg) {
            $parts = $arg->getParts();
            if ($parts !== null) {
                foreach($parts as $part) {
                    $result[] = $part;
                }
            }
        }
        return $result;
    }

    public function __toString(): string {
        return (string) self::toString($this->getCommandline());
    }

    /**
     * Put quotes around the given String if necessary.
     *
     * <p>If the argument doesn't include spaces or quotes, return it
     * as is. If it contains double quotes, use single quotes - else
     * surround the argument by double quotes.</p>
     *
     * @exception BuildException if the argument contains both, single
     *                           and double quotes.
     */
    public static function quoteArgument($argument) {
        if (str_contains((string) $argument, "\"")) {
            if (str_contains((string) $argument, "'")) {
                throw new BuildException("Can't handle single and double quotes in same argument");
            } else {
                return escapeshellarg((string) $argument);
            }
        } elseif (str_contains((string) $argument, "'") || str_contains((string) $argument, " ")) {
            return escapeshellarg((string) $argument);
            //return '\"' . $argument . '\"';
        } else {
            return $argument;
        }
    }

    /**
     * Quotes the parts of the given array in way that makes them
     * usable as command line arguments.
     */
    public static function toString($lines) {
        // empty path return empty string
        if (!$lines) {
            return "";
        }

        // path containing one or more elements
        $result = "";
        for ($i = 0, $len=count($lines); $i < $len; $i++) {
            if ($i > 0) {
                $result .= ' ';
            }
            $result .= self::quoteArgument($lines[$i]);
        }
        return $result;
    }

    /**
     *
     * @param string $to_process
     * @return array
     */
    public static function translateCommandline($to_process) {

        if (!$to_process) {
            return [];
        }

        // parse with a simple finite state machine

        $normal = 0;
        $inQuote = 1;
        $inDoubleQuote = 2;

        $state = $normal;
        $args = [];
        $current = "";
        $lastTokenHasBeenQuoted = false;

        $tok = strtok($to_process, "");
        $tokens = preg_split('/(["\' ])/', $to_process, -1, PREG_SPLIT_DELIM_CAPTURE);
        while(($nextTok = array_shift($tokens)) !== null) {
            switch ($state) {
            case $inQuote:
                if ("'" == $nextTok) {
                    $lastTokenHasBeenQuoted = true;
                    $state = $normal;
                } else {
                    $current .= $nextTok;
                }
                break;
            case $inDoubleQuote:
                if ("\"" == $nextTok) {
                    $lastTokenHasBeenQuoted = true;
                    $state = $normal;
                } else {
                    $current .= $nextTok;
                }
                break;
            default:
                if ("'" == $nextTok) {
                    $state = $inQuote;
                } elseif ("\"" == $nextTok) {
                    $state = $inDoubleQuote;
                } elseif (" " == $nextTok) {
                    if ($lastTokenHasBeenQuoted || strlen($current) != 0) {
                        $args[] = $current;
                        $current = "";
                    }
                } else {
                    $current .= $nextTok;
                }
                $lastTokenHasBeenQuoted = false;
                break;
            }
        }

        if ($lastTokenHasBeenQuoted || strlen($current) != 0) {
            $args[] = $current;
        }

        if ($state == $inQuote || $state == $inDoubleQuote) {
            throw new BuildException("unbalanced quotes in " . $to_process);
        }

        return $args;
    }

    /**
     * @return int Number of components in current commandline.
     */
    public function size() {
        return count($this->getCommandline());
    }

    public function __copy() {
        $c = new Commandline();
        $c->setExecutable($this->executable);
        $c->addArguments($this->getArguments());
        return $c;
    }

    /**
     * Clear out the whole command line.  */
    public function clear() {
        $this->executable = null;
        $this->arguments->removeAllElements();
    }

    /**
     * Clear out the arguments but leave the executable in place for
     * another operation.
     */
    public function clearArgs() {
        $this->arguments = [];
    }

    /**
     * Return a marker.
     *
     * <p>This marker can be used to locate a position on the
     * commandline - to insert something for example - when all
     * parameters have been set.</p>
     * @return CommandlineMarker
     */
    public function createMarker() {
        return new CommandlineMarker($this, count($this->arguments));
    }

    /**
     * Returns a String that describes the command and arguments
     * suitable for verbose output before a call to
     * <code>Runtime.exec(String[])<code>.
     *
     * <p>This method assumes that the first entry in the array is the
     * executable to run.</p>
     * @param array $args CommandlineArgument[] to use
     * @return string
     */
    public function describeCommand($args = null) {

        if ($args === null) {
            $args = $this->getCommandline();
        }

        if (!$args) {
            return "";
        }

        $buf = "Executing '";
        $buf .= $args[0];
        $buf .= "'";
        if (count($args) > 0) {
            $buf .= " with ";
            $buf .= $this->describeArguments($args, 1);
        } else {
            $buf .= self::DISCLAIMER;
        }
        return $buf;
    }

    /**
     * Returns a String that describes the arguments suitable for
     * verbose output before a call to
     * <code>Runtime.exec(String[])<code>
     * @param $args arguments to use (default is to use current class args)
     * @param $offset ignore entries before this index
     * @return string
     */
    protected function describeArguments($args = null, $offset = 0) {
        if ($args === null) {
            $args = $this->getArguments();
        }

        if ($args === null || count($args) <= $offset) {
            return "";
        }

        $buf = "argument";
        if (count($args) > $offset) {
            $buf .= "s";
        }
        $buf .= ":" . Phing::getProperty("line.separator");
        for ($i = $offset, $alen=count($args); $i < $alen; $i++) {
            $buf .= "'" . $args[$i] . "'" . Phing::getProperty("line.separator");
        }
        $buf .= self::DISCLAIMER;
        return $buf;
    }
}
