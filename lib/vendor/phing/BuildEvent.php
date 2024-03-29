<?php
/*
 *  $Id: BuildEvent.php 3076 2006-12-18 08:52:12Z fabien $
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
 * Encapsulates a build specific event.
 *
 * <p>We have three sources of events all handled by this class:
 *
 * <ul>
 *  <li>Project level events</li>
 *  <li>Target level events</li>
 *  <li>Task level events</li>
 * </ul>
 *
 * <p> Events are all fired from the project class by creating an event object
 * using this class and passing it to the listeners.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.10 $
 * @package   phing
 */
class BuildEvent extends EventObject {

    /**
     *  A reference to the project
     *  @var Project
     */
    protected $project;

    /**
     *  A reference to the target
     *  @var Target
     */
    protected $target;

    /**
     *  A reference to the task
     *
     *  @var Task
     */
    protected $task;

    /**
     *  The message of this event, if the event is a message
     *  @var    string
     *  @access private
     */
    protected $message = null;

    /**
     *  The priority of the message
     *
     *  @var    string
     *  @see    $message
     *  @access private
     */
    protected $priority = Project::PROJECT_MSG_VERBOSE;

    /**
     *  The execption that caused the event, if any
     *
     *  @var    object
     *  @access private
     */
    protected $exception = null;

    /**
     *  Construct a BuildEvent for a project, task or target source event
     *
     *  @param  object  project the project that emitted the event.
     *  @access public
     */
    function __construct($source) {
        parent::__construct($source);
        if ($source instanceof Project) {
            $this->project = $source;
            $this->target = null;
            $this->task = null;
        } elseif ($source instanceof Target) {
            $this->project = $source->getProject();
            $this->target = $source;
            $this->task = null;
        } elseif ($source instanceof Task) {
            $this->project = $source->getProject();
            $this->target = $source->getOwningTarget();
            $this->task = $source;
        } else {
            throw new Exception("Can not construct BuildEvent, unknown source given.");
        }
    }

    /**
     *  Sets the message with details and the message priority for this event.
     *
     *  @param  string   The string message of the event
     *  @param  integer  The priority this message should have
     */
    function setMessage($message, $priority) {
        $this->message = (string) $message;
        $this->priority = (int) $priority;
    }

    /**
     *  Set the exception that was the cause of this event.
     *
     *  @param  Exception The exception that caused the event
     */
    function setException($exception) {
        $this->exception = $exception;
    }

    /**
     *  Returns the project instance that fired this event.
     *
     *  The reference to the project instance is set by the constructor if this
     *  event was fired from the project class.
     *
     *  @return  Project  The project instance that fired this event
     */
    function getProject() {
        return $this->project;
    }

    /**
     *  Returns the target instance that fired this event.
     *
     *  The reference to the target instance is set by the constructor if this
     *  event was fired from the target class.
     *
     *  @return  object  The target that fired this event
     *  @access  public
     */
    function getTarget() {
        return $this->target;
    }

    /**
     *  Returns the target instance that fired this event.
     *
     *  The reference to the task instance is set by the constructor if this
     *  event was fired within a task.
     *
     *  @return  object  The task that fired this event
     *  @access  public
     */
    function getTask() {
        return $this->task;
    }

    /**
     *  Returns the logging message. This field will only be set for
     *  "messageLogged" events.
     *
     *  @return  string   The log message
     *  @access  public
     */
    function getMessage() {
        return $this->message;
    }

    /**
     *  Returns the priority of the logging message. This field will only
     *  be set for "messageLogged" events.
     *
     *  @return  integer  The message priority
     *  @access  public
     */
    function getPriority() {
        return $this->priority;
    }

    /**
     *  Returns the exception that was thrown, if any.
     *  This field will only be set for "taskFinished", "targetFinished", and
     *  "buildFinished" events.
     *
     *  @see BuildListener::taskFinished()
     *  @see BuildListener::targetFinished()
     *  @see BuildListener::buildFinished()
     */
    function getException() {
        return $this->exception;
    }
}
