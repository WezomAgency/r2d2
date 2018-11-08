<?php

namespace WezomAgency;

/**
 * Class R2D2
 * @package WezomAgency
 */
class R2D2
{
    private static $instance = null;

    /**
     * @return R2D2
     */
    public static function eject()
    {
        if (self::$instance instanceof R2D2) {
            return self::$instance;
        }
        return self::$instance = new R2D2();
    }

    /** @type string */
    protected $rootPath = '';
    /** @type string */
    protected $resourceRelativePath = '';
    /** @type string */
    protected $host = '';
    /** @type string */
    protected $protocol = 'http://';
    /** @type string */
    protected $svgSpritemapPath = '';
    /** @type bool */
    protected $debug = false;

    /**
     * @param string $key
     * @param string $value
     * @return R2D2 $this
     * @throws \Exception
     */
    public function set($key, $value)
    {
        if (property_exists($this, $key) === false) {
            if ($this->debug) {
                throw new \Exception("Property $key does not exist in class " . __CLASS__);
            } else {
                return $this;
            }
        }
        switch ($key) {
            case 'rootPath':
            case 'resourceRelativePath':
                $this->$key = rtrim($value, '/') . '/';
                break;
            default:
                $this->$key = $value;
        }

        return $this;
    }


    /**
     * @param string $url
     * @param boolean $timestamp
     * @param boolean $absolute
     * @return string
     */
    public function fileUrl($url, $timestamp = false, $absolute = false)
    {
        $file = trim($url, '/');
        return implode('', [
            $absolute ? ($this->protocol . $this->host) : '/',
            $file,
            $timestamp ? ('?time=' . fileatime($this->rootPath . $file)) : ''
        ]);
    }

    /**
     * @param string $path
     * @return bool|string
     */
    public function fileContent($path)
    {
        $path = $this->fileUrl($path, false, false);
        return file_get_contents($this->rootPath . $path);
    }


    /**
     * @param string $url
     * @param boolean $timestamp
     * @param boolean $absolute
     * @return string
     */
    public function resourceUrl($url, $timestamp = false, $absolute = false)
    {
        return $this->fileUrl($this->resourceRelativePath . ltrim($url, '/'), $timestamp, $absolute);
    }

    /**
     * @param string $path
     * @return bool|string
     */
    public function resourceContent($path)
    {
        return $this->fileContent($this->resourceRelativePath . ltrim($path, '/'));
    }


    /**
     * @param string $value
     * @return string
     */
    public function attrTextValue($value)
    {
        $text = strip_tags($value);
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    public function attr($name, $value)
    {
        if (is_numeric($name)) {
            return $value;
        }

        if (is_bool($value) && $name !== 'value') {
            return $value ? $name : '';
        }

        if (is_array($value) && $name === 'class') {
            return 'class="' . implode(' ', $value) . '"';
        }

        if (!is_null($value)) {
            return $name . '="' . $this->attrTextValue($value) . '"';
        }
    }

    /**
     * @param array $attrs
     * @return string
     */
    public function attrs($attrs)
    {
        $html = [];
        foreach ($attrs as $name => $value) {
            $element = $this->attr($name, $value);
            if (!is_null($element)) {
                $html[] = $element;
            }
        }
        return count($html) > 0 ? ' ' . implode(' ', $html) : '';
    }


    /**
     * @param string $id
     * @param array $attrs
     * @return string
     */
    public function svgSymbol($id, $attrs = [])
    {
        $svgAttributes = $this->attrs($attrs);
        $useHref = $this->svgSpritemapPath . '#' . $id;
        return '<svg ' . $svgAttributes . '><use xlink:href="' . $useHref . '"></use></svg>';
    }
}
