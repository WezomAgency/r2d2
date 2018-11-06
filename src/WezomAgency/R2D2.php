<?php

namespace WezomAgency;

class R2D2
{
    /** @type string */
    private static $rootPath = '';

    /** @param string $rootPath */
    public static function setRootPath($rootPath)
    {
        self::$rootPath = rtrim($rootPath, '/') . '/';
    }

    /** @return string */
    public static function getRootPath()
    {
        return self::$rootPath;
    }


    /** @type string */
    private static $resourceRelativePath = '';

    /** @param string $resourceRelativePath */
    public static function setResourceRelativePath($resourceRelativePath)
    {
        self::$resourceRelativePath = rtrim($resourceRelativePath, '/') . '/';
    }

    /** @return string */
    public static function getResourceRelativePath()
    {
        return self::$resourceRelativePath;
    }



    /** @type string */
    private static $host = '';

    /** @param string $host */
    public static function setHost ($host)
    {
        self::$host = $host;
    }

    /** @return string */
    public static function getHost ()
    {
        return self::$host;
    }



    /** @type string */
    private static $protocol = 'http://';

    /** @param string $protocol */
    public static function setProtocol ($protocol)
    {
        self::$protocol = $protocol;
    }

    /** @return string */
    public static function getProtocol ()
    {
        return self::$protocol;
    }



    /**
     * @param string $url
     * @param boolean $timestamp
     * @param boolean $absolute
     * @return string
     */
    public static function fileUrl ($url, $timestamp = false, $absolute = false)
    {
        $file = trim($url, '/');
        return implode('', [
            $absolute ? (self::getProtocol() . self::getHost()) : '/',
            $file,
            $timestamp ? ('?time=' . fileatime(self::getRootPath() . $file)) : ''
        ]);
    }

    /**
     * @param string $path
     * @return bool|string
     */
    public static function fileContent ($path)
    {
        $path = self::fileUrl($path, false, false);
        return file_get_contents(self::getRootPath() . $path);
    }



    /**
     * @param string $url
     * @param boolean $timestamp
     * @param boolean $absolute
     * @return string
     */
    public static function resourceUrl($url, $timestamp = false, $absolute = false)
    {
        return self::fileUrl(self::getResourceRelativePath() . ltrim($url, '/'), $timestamp, $absolute);
    }

    /**
     * @param string $path
     * @return bool|string
     */
    public static function resourceContent($path)
    {
        return self::fileContent(self::getResourceRelativePath() . ltrim($path,'/'));
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
            return $name . '="' . self::attrTextValue($value) . '"';
        }
    }

    /**
     * @param array $attrs
     * @return string
     */
    public static function attrs ($attrs)
    {
        $html = [];
        foreach ($attrs as $name => $value) {
            $element = self::attr($name, $value);
            if (!is_null($element)) {
                $html[] = $element;
            }
        }
        return count($html) > 0 ? ' ' . implode(' ', $html) : '';
    }



    /** @type string */
    private static $svgSpritemapPath = null;

    /** @param string $svgSpritemapPath */
    public static function setSvgSpritemapPath($svgSpritemapPath)
    {
        self::$svgSpritemapPath = $svgSpritemapPath;
    }

    /** @return string */
    public static function getSvgSpritemapPath()
    {
        return self::$svgSpritemapPath;
    }

    /**
     * @param string $id
     * @param array $attrs
     * @return string
     */
    public static function svgSymbol($id, $attrs = [])
    {
        $svgAttributes = self::attrs($attrs);
        $useHref = self::getSvgSpritemapPath() . '#' . $id;
        return '<svg ' . $svgAttributes . '><use xlink:href="' . $useHref . '"></use></svg>';
    }
}
