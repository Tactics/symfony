<?php

/*
 *  $Id: PropelOMTask.php 536 2007-01-10 14:30:38Z heltem $
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

require_once 'propel/phing/AbstractPropelDataModelTask.php';
include_once 'propel/engine/builder/om/ClassTools.php';
require_once 'propel/engine/builder/om/OMBuilder.php';

/**
 * This Task creates the OM classes based on the XML schema file.
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.phing
 */
class PropelOMTask extends AbstractPropelDataModelTask {

	/**
	 * The platform (php4, php5, etc.) for which the om is being built.
	 * @var        string
	 */
	private $targetPlatform;

	/**
	 * Sets the platform (php4, php5, etc.) for which the om is being built.
	 * @param      string $v
	 */
	public function setTargetPlatform($v) {
		$this->targetPlatform = $v;
	}

	/**
	 * Gets the platform (php4, php5, etc.) for which the om is being built.
	 * @return     string
	 */
	public function getTargetPlatform() {
		return $this->targetPlatform;
	}

	/**
	 * Utility method to create directory for package if it doesn't already exist.
	 * @param      string $path The [relative] package path.
	 * @throws     BuildException - if there is an error creating directories
	 */
	protected function ensureDirExists($path)
	{
		$f = new PhingFile($this->getOutputDirectory(), $path);
		if (!$f->exists()) {
			if (!$f->mkdirs()) {
				throw new BuildException("Error creating directories: ". $f->getPath());
			}
		}
	}

	/**
	 * Uses a builder class to create the output class.
	 * This method assumes that the DataModelBuilder class has been initialized with the build properties.
	 * @param      OMBuilder $builder
	 * @param      boolean $overwrite Whether to overwrite existing files with te new ones (default is YES).
	 * @todo       -cPropelOMTask Consider refactoring build() method into AbstractPropelDataModelTask (would need to be more generic).
	 */
	protected function build(OMBuilder $builder, $overwrite = true)
	{

		$path = $builder->getClassFilePath();
		$this->ensureDirExists(dirname($path));

		$_f = new PhingFile($this->getOutputDirectory(), $path);
		if ($overwrite || !$_f->exists()) {
			$this->log("\t\t-> " . $builder->getClassname() . " [builder: " . $builder::class . "]");
			$script = $builder->build();
			file_put_contents($_f->getAbsolutePath(), $script);
			foreach($builder->getWarnings() as $warning) {
				$this->log($warning, Project::PROJECT_MSG_WARN);
			}
		} else {
			$this->log("\t\t-> (exists) " . $builder->getClassname());
		}

	}

	/**
	 * Main method builds all the targets for a typical propel project.
	 */
	public function main()
	{
		// check to make sure task received all correct params
		$this->validate();

		$basepath = $this->getOutputDirectory();

		// Get new Capsule context
		$generator = $this->createContext();
		$generator->put("basepath", $basepath); // make available to other templates

		$targetPlatform = $this->getTargetPlatform(); // convenience for embedding in strings below

		// we need some values that were loaded into the template context
		$basePrefix = $generator->get('basePrefix');
		$project = $generator->get('project');

		DataModelBuilder::setBuildProperties($this->getPropelProperties());

		foreach ($this->getDataModels() as $dataModel) {
			$this->log("Processing Datamodel : " . $dataModel->getName());

			foreach ($dataModel->getDatabases() as $database) {

				$this->log("  - processing database : " . $database->getName());
				$generator->put("platform", $database->getPlatform());


				foreach ($database->getTables() as $table) {

					if (!$table->isForReferenceOnly()) {

						$this->log("\t+ " . $table->getName());

						// -----------------------------------------------------------------------------------------
						// Create Peer, Object, and MapBuilder classes
						// -----------------------------------------------------------------------------------------

						// these files are always created / overwrite any existing files
						foreach(['peer', 'object', 'mapbuilder'] as $target) {
							$builder = DataModelBuilder::builderFactory($table, $target);
							$this->build($builder);
						}

						// -----------------------------------------------------------------------------------------
						// Create [empty] stub Peer and Object classes if they don't exist
						// -----------------------------------------------------------------------------------------

						// these classes are only generated if they don't already exist
						foreach(['peerstub', 'objectstub'] as $target) {
							$builder = DataModelBuilder::builderFactory($table, $target);
							$this->build($builder, $overwrite=false);
						}

						// -----------------------------------------------------------------------------------------
						// Create [empty] stub child Object classes if they don't exist
						// -----------------------------------------------------------------------------------------

						// If table has enumerated children (uses inheritance) then create the empty child stub classes if they don't already exist.
						if ($table->getChildrenColumn()) {
							$col = $table->getChildrenColumn();
							if ($col->isEnumeratedClasses()) {
								foreach ($col->getChildren() as $child) {
									$builder = DataModelBuilder::builderFactory($table, 'objectmultiextend');
									$builder->setChild($child);
									$this->build($builder, $overwrite=false);
								} // foreach
							} // if col->is enumerated
						} // if tbl->getChildrenCol


						// -----------------------------------------------------------------------------------------
						// Create [empty] Interface if it doesn't exist
						// -----------------------------------------------------------------------------------------

						// Create [empty] interface if it does not already exist
						if ($table->getInterface()) {
							$builder = DataModelBuilder::builderFactory($table, 'interface');
							$this->build($builder, $overwrite=false);
						}

						// -----------------------------------------------------------------------------------------
						// Create tree Node classes
						// -----------------------------------------------------------------------------------------

						if ($table->isTree()) {

							foreach(['nodepeer', 'node'] as $target) {
								$builder = DataModelBuilder::builderFactory($table, $target);
								$this->build($builder);
							}

							foreach(['nodepeerstub', 'nodestub'] as $target) {
								$builder = DataModelBuilder::builderFactory($table, $target);
								$this->build($builder, $overwrite=false);
							}

						} // if Table->isTree()


					} // if !$table->isForReferenceOnly()

				} // foreach table

			} // foreach database

		} // foreach dataModel


	} // main()
}
