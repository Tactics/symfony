<?php
/*
 *  $Id: MultipleChoiceInputRequest.php 3076 2006-12-18 08:52:12Z fabien $
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
 * Encapsulates an input request.
 *
 * @author Stefan Bodewig <stefan.bodewig@epost.de>
 * @version $Revision: 1.5 $
 * @package phing.input
 */
class MultipleChoiceInputRequest extends InputRequest {

    /**
     * @param string $prompt The prompt to show to the user.  Must not be null.
     * @param array $choices holds all input values that are allowed.
     *                Must not be null.
     */
    public function __construct($prompt, protected $choices) {
        parent::__construct($prompt);
    }

    /**
     * @return The possible values.
     */
    public function getChoices() {
        return $this->choices;
    }

    /**
     * @return true if the input is one of the allowed values.
     */
    public function isInputValid() {
        return in_array($this->getInput(), $this->choices); // not strict (?)
    }
}
