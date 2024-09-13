<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDomCssSelector allows to navigate a DOM with CSS selector.
 *
 * based on getElementsBySelector version 0.4 - Simon Willison, March 25th 2003
 * http://simon.incutio.com/archive/2003/03/25/getElementsBySelector
 *
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @version    SVN: $Id: sfDomCssSelector.class.php 10947 2008-08-19 14:11:06Z fabien $
 */
class sfDomCssSelector
{
    public function __construct(protected $dom)
    {
    }

    public function getTexts($selector)
    {
        $texts = [];
        foreach ($this->getElements($selector) as $element) {
            $texts[] = $element->nodeValue;
        }

        return $texts;
    }

    public function getElements($selector)
    {
        $all_nodes = [];
        foreach ($this->tokenize_selectors($selector) as $selector) {
            $nodes = [$this->dom];
            foreach ($this->tokenize($selector) as $token) {
                $combinator = $token['combinator'];
                $token = trim((string) $token['name']);
                $pos = strpos($token, '#');
                if (false !== $pos && preg_match('/^[A-Za-z0-9]*$/', substr($token, 0, $pos))) {
                    // Token is an ID selector
                    $tagName = substr($token, 0, $pos);
                    $id = substr($token, $pos + 1);
                    $xpath = new DOMXPath($this->dom);
                    $element = $xpath->query(sprintf("//*[@id = '%s']", $id))->item(0);
                    if (!$element || ($tagName && strtolower($element->nodeName) != $tagName)) {
                        // tag with that ID not found
                        return [];
                    }

                    // Set nodes to contain just this element
                    $nodes = [$element];

                    continue; // Skip to next token
                }

                $pos = strpos($token, '.');
                if (false !== $pos && preg_match('/^[A-Za-z0-9]*$/', substr($token, 0, $pos))) {
                    // Token contains a class selector
                    $tagName = substr($token, 0, $pos);
                    if (!$tagName) {
                        $tagName = '*';
                    }
                    $className = substr($token, $pos + 1);

                    // Get elements matching tag, filter them for class selector
                    $founds = $this->getElementsByTagName($nodes, $tagName, $combinator);
                    $nodes = [];
                    foreach ($founds as $found) {
                        if (preg_match('/\b'.$className.'\b/', (string) $found->getAttribute('class'))) {
                            $nodes[] = $found;
                        }
                    }

                    continue; // Skip to next token
                }

                // Code to deal with attribute selectors
                if (preg_match('/^(\w*)(\[.+\])$/', $token, $matches)) {
                    $tagName = $matches[1] ?: '*';
                    preg_match_all('/
            \[
              ([\w\-]+)             # attribute
              ([=~\|\^\$\*]?)       # modifier (optional)
              =?                    # equal (optional)
              (
                "([^"]*)"           # quoted value (optional)
                |
                ([^\]]*)            # non quoted value (optional)
              )
            \]
          /x', $matches[2], $matches, PREG_SET_ORDER);

                    // Grab all of the tagName elements within current node
                    $founds = $this->getElementsByTagName($nodes, $tagName, $combinator);
                    $nodes = [];
                    foreach ($founds as $found) {
                        $ok = false;
                        foreach ($matches as $match) {
                            $attrName = $match[1];
                            $attrOperator = $match[2];
                            $attrValue = $match[4];

                            $ok = match ($attrOperator) {
                                '=' => $found->getAttribute($attrName) == $attrValue,
                                '~' => preg_match('/\b'.preg_quote($attrValue, '/').'\b/', (string) $found->getAttribute($attrName)),
                                '|' => preg_match('/^'.preg_quote($attrValue, '/').'-?/', (string) $found->getAttribute($attrName)),
                                '^' => str_starts_with((string) $found->getAttribute($attrName), $attrValue),
                                '$' => str_ends_with((string) $found->getAttribute($attrName), $attrValue),
                                '*' => str_contains((string) $found->getAttribute($attrName), $attrValue),
                                default => $found->hasAttribute($attrName),
                            };

                            if (false == $ok) {
                                break;
                            }
                        }

                        if ($ok) {
                            $nodes[] = $found;
                        }
                    }

                    continue; // Skip to next token
                }

                // If we get here, token is JUST an element (not a class or ID selector)
                $nodes = $this->getElementsByTagName($nodes, $token, $combinator);
            }

            foreach ($nodes as $node) {
                if (!$node->getAttribute('sf_matched')) {
                    $node->setAttribute('sf_matched', true);
                    $all_nodes[] = $node;
                }
            }
        }

        foreach ($all_nodes as $node) {
            $node->removeAttribute('sf_matched');
        }

        return $all_nodes;
    }

    protected function getElementsByTagName($nodes, $tagName, $combinator = ' ')
    {
        $founds = [];
        foreach ($nodes as $node) {
            switch ($combinator) {
                case ' ':
                    // Descendant selector
                    foreach ($node->getElementsByTagName($tagName) as $element) {
                        $founds[] = $element;
                    }
                    break;
                case '>':
                    // Child selector
                    foreach ($node->childNodes as $element) {
                        if ($tagName == $element->nodeName) {
                            $founds[] = $element;
                        }
                    }
                    break;
                case '+':
                    // Adjacent selector
                    $element = $node->nextSibling;
                    if ($element && '#text' == $element->nodeName) {
                        $element = $element->nextSibling;
                    }

                    if ($element && $tagName == $element->nodeName) {
                        $founds[] = $element;
                    }
                    break;
            }
        }

        return $founds;
    }

    protected function tokenize_selectors($selector)
    {
        // split tokens by , except in an attribute selector
        $tokens = [];
        $quoted = false;
        $token = '';
        for ($i = 0, $max = strlen((string) $selector); $i < $max; ++$i) {
            if (',' == $selector[$i] && !$quoted) {
                $tokens[] = trim($token);
                $token = '';
            } elseif ('"' == $selector[$i]) {
                $token .= $selector[$i];
                $quoted = $quoted ? false : true;
            } else {
                $token .= $selector[$i];
            }
        }
        if ($token) {
            $tokens[] = trim($token);
        }

        return $tokens;
    }

    protected function tokenize($selector)
    {
        // split tokens by space except if space is in an attribute selector
        $tokens = [];
        $combinators = [' ', '>', '+'];
        $quoted = false;
        $token = ['combinator' => ' ', 'name' => ''];
        for ($i = 0, $max = strlen((string) $selector); $i < $max; ++$i) {
            if (in_array($selector[$i], $combinators) && !$quoted) {
                // remove all whitespaces around the combinator
                $combinator = $selector[$i];
                while (in_array($selector[$i + 1], $combinators)) {
                    if (' ' != $selector[++$i]) {
                        $combinator = $selector[$i];
                    }
                }

                $tokens[] = $token;
                $token = ['combinator' => $combinator, 'name' => ''];
            } elseif ('"' == $selector[$i]) {
                $token['name'] .= $selector[$i];
                $quoted = $quoted ? false : true;
            } else {
                $token['name'] .= $selector[$i];
            }
        }
        if ($token['name']) {
            $tokens[] = $token;
        }

        return $tokens;
    }
}
