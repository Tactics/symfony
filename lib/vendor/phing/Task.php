<?php
/*
 *  $Id: Task.php 3076 2006-12-18 08:52:12Z fabien $
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
 * The base class for all Tasks.
 *
 * Use {@link Project#createTask} to register a new Task.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright � 2001,2002 THYRELL. All rights reserved
 * @version   $Revision: 1.11 $
 * @see       Project#createTask()
 * @package   phing
 */
abstract class Task extends ProjectComponent {

    /** owning Target object */
    protected $target;

    /** description of the task */
    protected $description;

    /** internal taskname (req) */
    protected $taskType;

    /** taskname for logger */
    protected $taskName;

    /** stored buildfile location */
    protected $location;

    /** wrapper of the task */
    protected $wrapper;

    /**
     * Sets the owning target this task belongs to.
     *
     * @param   object  Reference to owning target
     * @access  public
     */
    function setOwningTarget(Target $target) {
        $this->target = $target;
    }

    /**
     *  Returns the owning target of this task.
     *
     *  @return  object    The target object that owns this task
     *  @access  public
     */
    function getOwningTarget() {
        return $this->target;
    }

    /**
     *  Returns the name of task, used only for log messages
     *
     *  @return  string  Name of this task
     *  @access  public
     */
    function getTaskName() {
        if ($this->taskName === null) {
            // if no task name is set, then it's possible
            // this task was created from within another task.  We don't
            // therefore know the XML tag name for this task, so we'll just
            // use the class name stripped of "task" suffix.  This is only
            // for log messages, so we don't have to worry much about accuracy.
            $class = static::class;
            return $class ? preg_replace('/task$/i', '', $class) : $class;
        }
        return $this->taskName;
    }

    /**
     *  Sets the name of this task for log messages
     *
     *  @return  string  A string representing the name of this task for log
     *  @access  public
     */
    function setTaskName($name) {
        $this->taskName = (string) $name;
    }

    /**
     *  Returns the name of the task under which it was invoked,
     *  usually the XML tagname
     *
     *  @return string The type of this task (XML Tag)
     */
    function getTaskType() {
        return $this->taskType;
    }

    /**
     *  Sets the type of the task. Usually this is the name of the XML tag
     *
     *  @param string The type of this task (XML Tag)
     */
    function setTaskType($name) {
        $this->taskType = (string) $name;
    }

	/**
	 * Returns a name
	 *
	 */
	protected function getRegisterSlot($slotName) {
		return Register::getSlot('task.' . $this->getTaskName() . '.' . $slotName);
	}

    /**
     *  Provides a project level log event to the task.
     *
     *  @param string  The message to log
     *  @param integer The priority of the message
     *  @see BuildEvent
     *  @see BuildListener
     */
    function log($msg, $level = Project::PROJECT_MSG_INFO) {
        $this->project->logObject($this, $msg, $level);
    }

    /**
     *  Sets a textual description of the task
     *
     *  @param    string    The text describing the task
     */
    public function setDescription($desc) {
        $this->description = $desc;
    }

    /**
     *  Returns the textual description of the task
     *
     *  @return  string  The text description of the task
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     *  Called by the parser to let the task initialize properly.
     *  Should throw a BuildException if something goes wrong with the build
     *
     *  This is abstract here, but may not be overloaded by subclasses.
     *
     * @throws BuildException
     */
    public function init() {
    }

    /**
     *  Called by the project to let the task do it's work. This method may be
     *  called more than once, if the task is invoked more than once. For
     *  example, if target1 and target2 both depend on target3, then running
     *  <em>phing target1 target2</em> will run all tasks in target3 twice.
     *
     *  Should throw a BuildException if someting goes wrong with the build
     *
     *  This is abstract here. Must be overloaded by real tasks.
     *
     *  @access  public
     */
    abstract function main();

    /**
     *  Returns the location within the buildfile this task occurs. Used
     *  by {@link BuildException} to give detailed error messages.
     *
     *  @return Location The location object describing the position of this
     *                   task within the buildfile.
     */
    function getLocation() {
        return $this->location;
    }

    /**
     *  Sets the location within the buildfile this task occurs. Called by
     *  the parser to set location information.
     *
     *  @return  object  The location object describing the position of this
     *                   task within the buildfile.
     *  @access  public
     */
    function setLocation(Location $location) {
        $this->location = $location;
    }

    /**
     *  Returns the wrapper object for runtime configuration
     *
     *  @return  object  The wrapper object used by this task
     *  @access  public
     */
    function getRuntimeConfigurableWrapper() {
        if ($this->wrapper === null) {
            $this->wrapper = new RuntimeConfigurable($this, $this->getTaskName());
        }
        return $this->wrapper;
    }

    /**
     *  Sets the wrapper object this task should use for runtime
     *  configurable elements.
     *
     *  @param   object  The wrapper object this task should use
     *  @access  public
     */
    function setRuntimeConfigurableWrapper(RuntimeConfigurable $wrapper) {
        $this->wrapper = $wrapper;
    }

    /**
     *  Configure this task if it hasn't been done already.
     *
     *  @access  public
     */
    function maybeConfigure() {
        if ($this->wrapper !== null) {
            $this->wrapper->maybeConfigure($this->project);
        }
    }

    /**
     *  Perfrom this task
     *
     *  @access  public
     */
    function perform() {

        try { // try executing task
            $this->project->fireTaskStarted($this);
            $this->maybeConfigure();
            $this->main();
            $this->project->fireTaskFinished($this, $null=null);
        } catch (Exception $exc) {
            if ($exc instanceof BuildException) {
                if ($exc->getLocation() === null) {
                    $exc->setLocation($this->getLocation());
                }
            }
            $this->project->fireTaskFinished($this, $exc);
            throw $exc;
        }
    }
}
