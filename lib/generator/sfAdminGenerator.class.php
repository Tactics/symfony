<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Admin generator.
 *
 * This class generates an admin module.
 *
 * This class calls two ORM specific methods:
 *   getAllColumns()
 * and
 *   getAdminColumnForField($field, $flag = null)
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfAdminGenerator.class.php 9861 2008-06-25 12:07:06Z fabien $
 */
abstract class sfAdminGenerator extends sfCrudGenerator
{
    protected $fields = [];

    /**
     * Returns HTML code for a help icon.
     *
     * @param string The column name
     * @param string The field type (list, edit)
     *
     * @return string HTML code
     */
    public function getHelpAsIcon($column, $type = '')
    {
        $help = $this->getParameterValue($type.'.fields.'.$column->getName().'.help');
        if ($help) {
            return "[?php echo image_tag(sfConfig::get('sf_admin_web_dir').'/images/help.png', array('align' => 'absmiddle', 'alt' => __('".$this->escapeString($help)."'), 'title' => __('".$this->escapeString($help)."'))) ?]";
        }

        return '';
    }

    /**
     * Returns HTML code for a help text.
     *
     * @param string The column name
     * @param string The field type (list, edit)
     *
     * @return string HTML code
     */
    public function getHelp($column, $type = '')
    {
        $help = $this->getParameterValue($type.'.fields.'.$column->getName().'.help');
        if ($help) {
            return "<div class=\"sf_admin_edit_help\">[?php echo __('".$this->escapeString($help)."') ?]</div>";
        }

        return '';
    }

    /**
     * Returns HTML code for an action button.
     *
     * @param string  The action name
     * @param array   The parameters
     * @param bool Whether to add a primary key link or not
     *
     * @return string HTML code
     */
    public function getButtonToAction($actionName, $params, $pk_link = false)
    {
        $params = (array) $params;
        $options = isset($params['params']) ? sfToolkit::stringToArray($params['params']) : [];
        $method = 'button_to';
        $li_class = '';
        $only_for = $params['only_for'] ?? null;

        // default values
        if ($actionName[0] == '_') {
            $actionName = substr((string) $actionName, 1);
            $default_name = strtr($actionName, '_', ' ');
            $default_icon = sfConfig::get('sf_admin_web_dir').'/images/'.$actionName.'_icon.png';
            $default_action = $actionName;
            $default_class = 'sf_admin_action_'.$actionName;

            if ($actionName == 'save' || $actionName == 'save_and_add' || $actionName == 'save_and_list') {
                $method = 'submit_tag';
                $options['name'] = $actionName;
            }

            if ($actionName == 'delete') {
                $options['post'] = true;
                if (!isset($options['confirm'])) {
                    $options['confirm'] = 'Are you sure?';
                }

                $li_class = 'float-left';

                $only_for = 'edit';
            }
        } else {
            $default_name = strtr($actionName, '_', ' ');
            $default_icon = sfConfig::get('sf_admin_web_dir').'/images/default_icon.png';
            $default_action = 'List'.sfInflector::camelize($actionName);
            $default_class = '';
        }

        $name = $params['name'] ?? $default_name;
        $icon = isset($params['icon']) ? sfToolkit::replaceConstants($params['icon']) : $default_icon;
        $action = $params['action'] ?? $default_action;
        $url_params = $pk_link ? '?'.$this->getPrimaryKeyUrlParams() : '\'';

        if (!isset($options['class'])) {
            if ($default_class) {
                $options['class'] = $default_class;
            } else {
                $options['style'] = 'background: #ffc url('.$icon.') no-repeat 3px 2px';
            }
        }

        $li_class = $li_class ? ' class="'.$li_class.'"' : '';

        $html = '<li'.$li_class.'>';

        if ($only_for == 'edit') {
            $html .= '[?php if ('.$this->getPrimaryKeyIsSet().'): ?]'."\n";
        } elseif ($only_for == 'create') {
            $html .= '[?php if (!'.$this->getPrimaryKeyIsSet().'): ?]'."\n";
        } elseif ($only_for !== null) {
            throw new sfConfigurationException(sprintf('The "only_for" parameter can only takes "create" or "edit" as argument ("%s")', $only_for));
        }

        if ($method == 'submit_tag') {
            $html .= '[?php echo submit_tag(__(\''.$name.'\'), '.var_export($options, true).') ?]';
        } else {
            $phpOptions = var_export($options, true);

            // little hack
            $phpOptions = $phpOptions ? preg_replace("/'confirm' => '(.+?)(?<!\\\)'/", '\'confirm\' => __(\'$1\')', $phpOptions) : $phpOptions;

            $html .= '[?php echo button_to(__(\''.$name.'\'), \''.$this->getModuleName().'/'.$action.$url_params.', '.$phpOptions.') ?]';
        }

        if ($only_for !== null) {
            $html .= '[?php endif; ?]'."\n";
        }

        $html .= '</li>'."\n";

        return $html;
    }

    /**
     * Returns HTML code for an action link.
     *
     * @param string  The action name
     * @param array   The parameters
     * @param bool Whether to add a primary key link or not
     *
     * @return string HTML code
     */
    public function getLinkToAction($actionName, $params, $pk_link = false)
    {
        $options = isset($params['params']) ? sfToolkit::stringToArray($params['params']) : [];

        // default values
        if ($actionName[0] == '_') {
            $actionName = substr((string) $actionName, 1);
            $name = $actionName;
            $icon = sfConfig::get('sf_admin_web_dir').'/images/'.$actionName.'_icon.png';
            $action = $actionName;

            if ($actionName == 'delete') {
                $options['post'] = true;
                if (!isset($options['confirm'])) {
                    $options['confirm'] = 'Are you sure?';
                }
            }
        } else {
            $name = $params['name'] ?? $actionName;
            $icon = isset($params['icon']) ? sfToolkit::replaceConstants($params['icon']) : sfConfig::get('sf_admin_web_dir').'/images/default_icon.png';
            $action = $params['action'] ?? 'List'.sfInflector::camelize($actionName);
        }

        $url_params = $pk_link ? '?'.$this->getPrimaryKeyUrlParams() : '\'';

        $phpOptions = var_export($options, true);

        // little hack
        $phpOptions = $phpOptions ? preg_replace("/'confirm' => '(.+?)(?<!\\\)'/", '\'confirm\' => __(\'$1\')', $phpOptions) : $phpOptions;

        return '<li>[?php echo link_to(image_tag(\''.$icon.'\', array(\'alt\' => __(\''.$name.'\'), \'title\' => __(\''.$name.'\'))), \''.$this->getModuleName().'/'.$action.$url_params.($options ? ', '.$phpOptions : '').') ?]</li>'."\n";
    }

    /**
     * Returns HTML code for a column in edit mode.
     *
     * @param string  The column name
     * @param array   The parameters
     *
     * @return string HTML code
     */
    public function getColumnEditTag($column, $params = [])
    {
        // user defined parameters
        $user_params = $this->getParameterValue('edit.fields.'.$column->getName().'.params');
        $user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
        $params = $user_params ? array_merge($params, $user_params) : $params;

        if ($column->isComponent()) {
            return "get_component('".$this->getModuleName()."', '".$column->getName()."', array('type' => 'edit', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
        } elseif ($column->isPartial()) {
            return "get_partial('".$column->getName()."', array('type' => 'edit', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
        }

        // default control name
        $params = array_merge(['control_name' => $this->getSingularName().'['.$column->getName().']'], $params);

        // default parameter values
        $type = $column->getCreoleType();
        if ($type == CreoleTypes::DATE) {
            $params = array_merge(['rich' => true, 'calendar_button_img' => sfConfig::get('sf_admin_web_dir').'/images/date.png'], $params);
        } elseif ($type == CreoleTypes::TIMESTAMP) {
            $params = array_merge(['rich' => true, 'withtime' => true, 'calendar_button_img' => sfConfig::get('sf_admin_web_dir').'/images/date.png'], $params);
        }

        // user sets a specific tag to use
        if ($inputType = $this->getParameterValue('edit.fields.'.$column->getName().'.type')) {
            if ($inputType == 'plain') {
                return $this->getColumnListTag($column, $params);
            } else {
                return $this->getPHPObjectHelper($inputType, $column, $params);
            }
        }

        // guess the best tag to use with column type
        return parent::getCrudColumnEditTag($column, $params);
    }

    /**
     * Returns all column categories.
     *
     * @param string  The parameter name
     *
     * @return array The column categories
     */
    public function getColumnCategories($paramName)
    {
        if (is_array($this->getParameterValue($paramName))) {
            $fields = $this->getParameterValue($paramName);

            // do we have categories?
            if (!isset($fields[0])) {
                return array_keys($fields);
            }
        }

        return ['NONE'];
    }

    /**
     * Wraps content with a credential condition.
     *
     * @param string  The content
     * @param array   The parameters
     *
     * @return string HTML code
     */
    public function addCredentialCondition($content, $params = [])
    {
        if (isset($params['credentials'])) {
            $credentials = str_replace("\n", ' ', var_export($params['credentials'], true));

            return <<<EOF
[?php if (\$sf_user->hasCredential($credentials)): ?]
$content
[?php endif; ?]
EOF;
        } else {
            return $content;
        }
    }

    /**
     * Gets sfAdminColumn objects for a given category.
     *
     * @param string The parameter name
     *
     * @return array sfAdminColumn array
     */
    public function getColumns($paramName, $category = 'NONE')
    {
        $phpNames = [];

        // user has set a personnalized list of fields?
        $fields = $this->getParameterValue($paramName);
        if (is_array($fields)) {
            // categories?
            if (isset($fields[0])) {
                // simulate a default one
                $fields = ['NONE' => $fields];
            }

            if (!$fields) {
                return [];
            }

            foreach ($fields[$category] as $field) {
                [$field, $flags] = $this->splitFlag($field);

                $phpNames[] = $this->getAdminColumnForField($field, $flags);
            }
        } else {
            // no, just return the full list of columns in table
            return $this->getAllColumns();
        }

        return $phpNames;
    }

    /**
     * Gets modifier flags from a column name.
     *
     * @param string The column name
     *
     * @return array An array of detected flags
     */
    public function splitFlag($text)
    {
        $flags = [];
        while (in_array($text[0], ['=', '-', '+', '_', '~'])) {
            $flags[] = $text[0];
            $text = substr((string) $text, 1);
        }

        return [$text, $flags];
    }

    /**
     * Gets a parameter value.
     *
     * @param string The key name
     * @param mixed  The default value
     *
     * @return mixed The parameter value
     */
    public function getParameterValue($key, $default = null)
    {
        if (preg_match('/^([^\.]+)\.fields\.(.+)$/', (string) $key, $matches)) {
            return $this->getFieldParameterValue($matches[2], $matches[1], $default);
        } else {
            return $this->getValueFromKey($key, $default);
        }
    }

    /**
     * Gets a field parameter value.
     *
     * @param string The key name
     * @param string The type (list, edit)
     * @param mixed  The default value
     *
     * @return mixed The parameter value
     */
    protected function getFieldParameterValue($key, $type = '', $default = null)
    {
        $retval = $this->getValueFromKey($type.'.fields.'.$key, $default);
        if ($retval !== null) {
            return $retval;
        }

        $retval = $this->getValueFromKey('fields.'.$key, $default);
        if ($retval !== null) {
            return $retval;
        }

        if (preg_match('/\.name$/', (string) $key)) {
            // default field.name
            return sfInflector::humanize(($pos = strpos((string) $key, '.')) ? substr((string) $key, 0, $pos) : $key);
        } else {
            return null;
        }
    }

    /**
     * Gets the value for a given key.
     *
     * @param string The key name
     * @param mixed  The default value
     *
     * @return mixed The key value
     */
    protected function getValueFromKey($key, $default = null)
    {
        $ref = &$this->params;
        $parts = explode('.', (string) $key);
        $count = count($parts);
        for ($i = 0; $i < $count; ++$i) {
            $partKey = $parts[$i];
            if (!isset($ref[$partKey])) {
                return $default;
            }

            if ($count == $i + 1) {
                return $ref[$partKey];
            } else {
                $ref = &$ref[$partKey];
            }
        }

        return $default;
    }

    /**
     * Wraps a content for I18N.
     *
     * @param string The key name
     * @param string The defaul value
     *
     * @return string HTML code
     */
    public function getI18NString($key, $default = null, $withEcho = true)
    {
        $value = $this->escapeString($this->getParameterValue($key, $default));

        // find %%xx%% strings
        preg_match_all('/%%([^%]+)%%/', $value, $matches, PREG_PATTERN_ORDER);
        $this->params['tmp']['display'] = [];
        foreach ($matches[1] as $name) {
            $this->params['tmp']['display'][] = $name;
        }

        $vars = [];
        foreach ($this->getColumns('tmp.display') as $column) {
            if ($column->isLink()) {
                $vars[] = '\'%%'.$column->getName().'%%\' => link_to('.$this->getColumnListTag($column).', \''.$this->getModuleName().'/edit?'.$this->getPrimaryKeyUrlParams().')';
            } elseif ($column->isPartial()) {
                $vars[] = '\'%%_'.$column->getName().'%%\' => '.$this->getColumnListTag($column);
            } elseif ($column->isComponent()) {
                $vars[] = '\'%%~'.$column->getName().'%%\' => '.$this->getColumnListTag($column);
            } else {
                $vars[] = '\'%%'.$column->getName().'%%\' => '.$this->getColumnListTag($column);
            }
        }

        // strip all = signs
        $value = $value ? preg_replace('/%%=([^%]+)%%/', '%%$1%%', $value) : $value;

        $i18n = '__(\''.$value.'\', '."\n".'array('.implode(",\n", $vars).'))';

        return $withEcho ? '[?php echo '.$i18n.' ?]' : $i18n;
    }

    /**
     * Replaces constants in a string.
     *
     * @param string
     *
     * @return string
     */
    public function replaceConstants($value)
    {
        // find %%xx%% strings
        preg_match_all('/%%([^%]+)%%/', (string) $value, $matches, PREG_PATTERN_ORDER);
        $this->params['tmp']['display'] = [];
        foreach ($matches[1] as $name) {
            $this->params['tmp']['display'][] = $name;
        }

        foreach ($this->getColumns('tmp.display') as $column) {
            $value = str_replace('%%'.$column->getName().'%%', '{'.$this->getColumnGetter($column, true, 'this->').'}', $value);
        }

        return $value;
    }

    /**
     * Returns HTML code for a column in list mode.
     *
     * @param string  The column name
     * @param array   The parameters
     *
     * @return string HTML code
     */
    public function getColumnListTag($column, $params = [])
    {
        $user_params = $this->getParameterValue('list.fields.'.$column->getName().'.params');
        $user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
        $params = $user_params ? array_merge($params, $user_params) : $params;

        $type = $column->getCreoleType();

        $columnGetter = $this->getColumnGetter($column, true);

        if ($column->isComponent()) {
            return "get_component('".$this->getModuleName()."', '".$column->getName()."', array('type' => 'list', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
        } elseif ($column->isPartial()) {
            return "get_partial('".$column->getName()."', array('type' => 'list', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
        } elseif ($type == CreoleTypes::DATE || $type == CreoleTypes::TIMESTAMP) {
            $format = $params['date_format'] ?? ($type == CreoleTypes::DATE ? 'D' : 'f');

            return "($columnGetter !== null && $columnGetter !== '') ? format_date($columnGetter, \"$format\") : ''";
        } elseif ($type == CreoleTypes::BOOLEAN) {
            return "$columnGetter ? image_tag(sfConfig::get('sf_admin_web_dir').'/images/tick.png') : '&nbsp;'";
        } else {
            return "$columnGetter";
        }
    }

    /**
     * Returns HTML code for a column in filter mode.
     *
     * @param string  The column name
     * @param array   The parameters
     *
     * @return string HTML code
     */
    public function getColumnFilterTag($column, $params = [])
    {
        $user_params = $this->getParameterValue('list.fields.'.$column->getName().'.params');
        $user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
        $params = $user_params ? array_merge($params, $user_params) : $params;

        if ($column->isComponent()) {
            return "get_component('".$this->getModuleName()."', '".$column->getName()."', array('type' => 'filter'))";
        } elseif ($column->isPartial()) {
            return "get_partial('".$column->getName()."', array('type' => 'filter', 'filters' => \$filters))";
        }

        $type = $column->getCreoleType();

        $default_value = "isset(\$filters['".$column->getName()."']) ? \$filters['".$column->getName()."'] : null";
        $unquotedName = 'filters['.$column->getName().']';
        $name = "'$unquotedName'";

        if ($column->isForeignKey()) {
            $params = $this->getObjectTagParams($params, ['include_blank' => true, 'related_class' => $this->getRelatedClassName($column), 'text_method' => '__toString', 'control_name' => $unquotedName]);

            return "object_select_tag($default_value, null, $params)";
        } elseif ($type == CreoleTypes::DATE) {
            // rich=false not yet implemented
            $params = $this->getObjectTagParams($params, ['rich' => true, 'calendar_button_img' => sfConfig::get('sf_admin_web_dir').'/images/date.png']);

            return "input_date_range_tag($name, $default_value, $params)";
        } elseif ($type == CreoleTypes::TIMESTAMP) {
            // rich=false not yet implemented
            $params = $this->getObjectTagParams($params, ['rich' => true, 'withtime' => true, 'calendar_button_img' => sfConfig::get('sf_admin_web_dir').'/images/date.png']);

            return "input_date_range_tag($name, $default_value, $params)";
        } elseif ($type == CreoleTypes::BOOLEAN) {
            $defaultIncludeCustom = '__("yes or no")';

            $option_params = $this->getObjectTagParams($params, ['include_custom' => $defaultIncludeCustom]);
            $params = $this->getObjectTagParams($params);

            // little hack
            $option_params = $option_params ? preg_replace("/'".preg_quote($defaultIncludeCustom)."'/", $defaultIncludeCustom, $option_params) : $option_params;

            $options = "options_for_select(array(1 => __('yes'), 0 => __('no')), $default_value, $option_params)";

            return "select_tag($name, $options, $params)";
        } elseif ($type == CreoleTypes::CHAR || $type == CreoleTypes::VARCHAR || $type == CreoleTypes::TEXT || $type == CreoleTypes::LONGVARCHAR) {
            $size = ($column->getSize() < 15 ? $column->getSize() : 15);
            $params = $this->getObjectTagParams($params, ['size' => $size]);

            return "input_tag($name, $default_value, $params)";
        } elseif ($type == CreoleTypes::INTEGER || $type == CreoleTypes::TINYINT || $type == CreoleTypes::SMALLINT || $type == CreoleTypes::BIGINT) {
            $params = $this->getObjectTagParams($params, ['size' => 7]);

            return "input_tag($name, $default_value, $params)";
        } elseif ($type == CreoleTypes::FLOAT || $type == CreoleTypes::DOUBLE || $type == CreoleTypes::DECIMAL || $type == CreoleTypes::NUMERIC || $type == CreoleTypes::REAL) {
            $params = $this->getObjectTagParams($params, ['size' => 7]);

            return "input_tag($name, $default_value, $params)";
        } else {
            $params = $this->getObjectTagParams($params, ['disabled' => true]);

            return "input_tag($name, $default_value, $params)";
        }
    }

    /**
     * Escapes a string.
     *
     * @param string
     * @param string
     *
     * @return string|string[]|null
     */
    protected function escapeString($string)
    {
        return $string ? preg_replace('/\'/', '\\\'', $string) : $string;
    }
}
