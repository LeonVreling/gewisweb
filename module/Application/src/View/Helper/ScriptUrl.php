<?php

namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Url view helper for use inside javascript code.
 * Usage: $this->scriptUrl()->requireUrl('/url/route');.
 */
class ScriptUrl extends AbstractHelper
{
    /**
     * Array of all urls to make available.
     *
     * @var array
     */
    protected $urls = [];

    /**
     * @return ScriptUrl
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Makes an url route available to the javascript url helper.
     *
     * @param string $name name of the route
     * @param array $params list of route parameters to make available
     *
     * @return ScriptUrl
     */
    public function requireUrl($name, $params = [])
    {
        $scriptParams = [];

        foreach ($params as $param) {
            $scriptParams[$param] = '{' . $param . '}';
        }

        $url = $this->getView()->url($name, $scriptParams);
        $this->urls[$name] = $url;

        return $this;
    }

    /**
     * Make multiple url routes available to the javascript url helper.
     * Only works with urls which have the same parameters.
     *
     * @param array $names list of route names
     * @param array $params list of route parameters to make available
     *
     * @return ScriptUrl
     */
    public function requireUrls($names, $params)
    {
        foreach ($names as $name) {
            $this->requireUrl($name, $params);
        }

        return $this;
    }

    /**
     * Returns the list of urls to feed to the javascript url helper.
     *
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }
}