<?php
/*
 *  $Id: TarTask.php 3076 2006-12-18 08:52:12Z fabien $
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
 * Creates a tar archive using PEAR Archive_Tar.
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Stefano Mazzocchi <stefano@apache.org> (Ant)
 * @author    Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @author    Magesh Umasankar
 * @version   $Revision: 1.10 $
 * @package   phing.tasks.ext
 */
class TarTask extends MatchingTask {

    const TAR_NAMELEN = 100;

    const WARN = "warn";
    const FAIL = "fail";
    const OMIT = "omit";

    private $tarFile;
    private $baseDir;
	private $includeEmpty = true; // Whether to include empty dirs in the TAR

    private $longFileMode = "warn";

    private $filesets = [];
    private $fileSetFiles = [];

    /**
     * Indicates whether the user has been warned about long files already.
     */
    private $longWarningGiven = false;

    /**
     * Compression mode.  Available options "gzip", "bzip2", "none" (null).
     */
    private $compression = null;

    /**
     * Ensures that PEAR lib exists.
     */
    public function init() {
        include_once 'Archive/Tar.php';
        if (!class_exists('Archive_Tar')) {
            throw new BuildException("You must have installed the PEAR Archive_Tar class in order to use TarTask.");
        }
    }

    /**
     * Add a new fileset
     * @return FileSet
     */
    public function createTarFileSet() {
        $this->fileset = new TarFileSet();
        $this->filesets[] = $this->fileset;
        return $this->fileset;
    }

    /**
     * Add a new fileset.  Alias to createTarFileSet() for backwards compatibility.
     * @return FileSet
     * @see createTarFileSet()
     */
    public function createFileSet() {
        $this->fileset = new TarFileSet();
        $this->filesets[] = $this->fileset;
        return $this->fileset;
    }

    /**
     * Set is the name/location of where to create the tar file.
     * @param PhingFile $destFile The output of the tar
     */
    public function setDestFile(PhingFile $destFile) {
        $this->tarFile = $destFile;
    }

    /**
     * This is the base directory to look in for things to tar.
     * @param PhingFile $baseDir
     */
    public function setBasedir(PhingFile $baseDir) {
        $this->baseDir = $baseDir;
    }

    /**
     * Set the include empty dirs flag.
     * @param  boolean  Flag if empty dirs should be tarred too
     * @return void
     * @access public
     */
    function setIncludeEmptyDirs($bool) {
        $this->includeEmpty = (boolean) $bool;
    }

    /**
     * Set how to handle long files, those with a path&gt;100 chars.
     * Optional, default=warn.
     * <p>
     * Allowable values are
     * <ul>
     * <li>  truncate - paths are truncated to the maximum length
     * <li>  fail - paths greater than the maximim cause a build exception
     * <li>  warn - paths greater than the maximum cause a warning and GNU is used
     * <li>  gnu - GNU extensions are used for any paths greater than the maximum.
     * <li>  omit - paths greater than the maximum are omitted from the archive
     * </ul>
     */
    public function setLongfile($mode) {
        $this->longFileMode = $mode;
    }

    /**
     * Set compression method.
     * Allowable values are
     * <ul>
     * <li>  none - no compression
     * <li>  gzip - Gzip compression
     * <li>  bzip2 - Bzip2 compression
     * </ul>
     */
    public function setCompression($mode) {
        switch($mode) {
            case "gzip":
                $this->compression = "gz";
                break;
            case "bzip2":
                $this->compression = "bz2";
                break;
            case "none":
                $this->compression = null;
                break;
            default:
                $this->log("Ignoring unknown compression mode: ".$mode, Project::PROJECT_MSG_WARN);
                $this->compression = null;
        }
    }

    /**
     * do the work
     * @throws BuildException
     */
    public function main() {

        if ($this->tarFile === null) {
            throw new BuildException("tarfile attribute must be set!", $this->getLocation());
        }

        if ($this->tarFile->exists() && $this->tarFile->isDirectory()) {
            throw new BuildException("tarfile is a directory!", $this->getLocation());
        }

        if ($this->tarFile->exists() && !$this->tarFile->canWrite()) {
            throw new BuildException("Can not write to the specified tarfile!", $this->getLocation());
        }

        // shouldn't need to clone, since the entries in filesets
        // themselves won't be modified -- only elements will be added
        $savedFileSets = $this->filesets;

        try {
            if ($this->baseDir !== null) {
                if (!$this->baseDir->exists()) {
                    throw new BuildException("basedir does not exist!", $this->getLocation());
                }
				if (empty($this->filesets)) { // if there weren't any explicit filesets specivied, then
	                						  // create a default, all-inclusive fileset using the specified basedir.
	                $mainFileSet = new TarFileSet($this->fileset);
	                $mainFileSet->setDir($this->baseDir);
	                $this->filesets[] = $mainFileSet;
	            }
            }

            if (empty($this->filesets)) {
                throw new BuildException("You must supply either a basedir "
                                         . "attribute or some nested filesets.",
                                         $this->getLocation());
            }

            // check if tar is out of date with respect to each fileset
            if($this->tarFile->exists()) {
	            $upToDate = true;
	            foreach($this->filesets as $fs) {
	                $files = $fs->getFiles($this->project, $this->includeEmpty);
	                if (!$this->archiveIsUpToDate($files, $fs->getDir($this->project))) {
	                    $upToDate = false;
	                }
	                for ($i=0, $fcount=count($files); $i < $fcount; $i++) {
	                    if ($this->tarFile->equals(new PhingFile($fs->getDir($this->project), $files[$i]))) {
	                        throw new BuildException("A tar file cannot include itself", $this->getLocation());
	                    }
	                }
	            }
	            if ($upToDate) {
	                $this->log("Nothing to do: " . $this->tarFile->__toString() . " is up to date.", Project::PROJECT_MSG_INFO);
	                return;
	            }
			}

            $this->log("Building tar: " . $this->tarFile->__toString(), Project::PROJECT_MSG_INFO);

            $tar = new Archive_Tar($this->tarFile->getAbsolutePath(), $this->compression);

            // print errors
            $tar->setErrorHandling(PEAR_ERROR_PRINT);

            foreach($this->filesets as $fs) {
                    $files = $fs->getFiles($this->project, $this->includeEmpty);
                    if (count($files) > 1 && strlen((string) $fs->getFullpath()) > 0) {
                        throw new BuildException("fullpath attribute may only "
                                                 . "be specified for "
                                                 . "filesets that specify a "
                                                 . "single file.");
                    }
                    $fsBasedir = $fs->getDir($this->project);
                    $filesToTar = [];
                    for ($i=0, $fcount=count($files); $i < $fcount; $i++) {
                        $f = new PhingFile($fsBasedir, $files[$i]);
                        $filesToTar[] = $f->getAbsolutePath();
                    }
                    $tar->addModify($filesToTar, '', $fsBasedir->getAbsolutePath());
            }


        } catch (IOException $ioe) {
                $msg = "Problem creating TAR: " . $ioe->getMessage();
                $this->filesets = $savedFileSets;
                throw new BuildException($msg, $ioe, $this->getLocation());
        }

        $this->filesets = $savedFileSets;
    }

    /**
     * @param array $files array of filenames
     * @param PhingFile $dir
     * @return boolean
     */
    protected function archiveIsUpToDate($files, $dir) {
        $sfs = new SourceFileScanner($this);
        $mm = new MergeMapper();
        $mm->setTo($this->tarFile->getAbsolutePath());
        return count($sfs->restrict($files, $dir, null, $mm)) == 0;
    }

}
