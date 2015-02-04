<?php

/**
 * Example controller class
class Users2
{
    public function edit2()
    {
        echo "Users2::edit2 -> id={$_GET['id']}\n";
    }

    public function view()
    {
        echo "Users2::view -> id={$_GET['id']}\n";
    }
}
*/

class Router
{
    const RESERVED_SYMBOLS   = '[.\\+*?[^\\]${}=!|]';
    const SEGMENT_DELIMITERS = '[^/.\-#]+';

    protected $_routes   = array();

    /**
     * @param string $route
     * @param array $params
     * @return $this
     * @throws Exception
     */
    public function addRoute($route, $params = array())
    {
        $route = preg_replace('~' . self::RESERVED_SYMBOLS . '~', '\\\\$0', $route);
        $route = str_replace(array('(', ')'), array('(?:', ')?'), $route);
        $route = str_replace(array('<', '>'), array('(?<', '>' . self::SEGMENT_DELIMITERS . ')'), $route);

        if(!isset($this->_routes[$route]))
            $this->_routes[$route] = array();

        foreach(array('controller', 'action', 'id') as $key)
            if(strpos($route, '<' . $key . '>') === false && (!isset($params[$key]) || !$params[$key]))
                throw new Exception('Default `' . $key . '` is not defined');

        $this->_routes[$route] = $params;

        return $this;
    }

    /**
     * @return object
     * @throws Exception
     */
    public function process($route)
    {
        foreach($this->_routes as $pattern => $params) {
            if(preg_match('~' . $pattern . '~i', $route, $matches)) {
                foreach($params as $key => $value)
                    if(!isset($matches[$key]) || !$matches[$key])
                        $matches[$key] = $value;

                foreach($matches as $key => $value)
                    if(!is_int($key) && !in_array($key, array('controller', 'action')))
                       $_GET[$key] = $value;

                return $this->_executeAction(
                    ucwords(strtolower($matches['controller'])),
                    strtolower($matches['action'])
                );
            }
        }

        throw new Exception('Error 404');
    }

    /**
     * @param string $controller
     * @param string $action
     * @throws Exception
     */
    protected function _executeAction($controller, $action)
    {
        if(!class_exists($controller))
            throw new Exception('Class ' . $controller . ' not found');

        $reflector = new ReflectionClass($controller);
        if(!$reflector->hasMethod($action))
            throw new Exception('Method ' . $controller . '::' . $action . ' is not defined');

        if(!$reflector->getMethod($action)->isPublic())
            throw new Exception('Method ' . $controller . '::' . $action . ' is not public');

        $obj = $reflector->newInstance();
        call_user_func_array(array($obj, $action), array());
    }
}

/*
$router = new Router();
$router->addRoute(
            'users:edit/<id>',
            array(
                'controller' => 'users2',
                'action' => 'edit2'
            )
        )
       ->addRoute(
            'orders_stats.<action>.<id>',
            array(
                'controller' => 'orders_stats2'
            )
        )
       ->addRoute('<controller>(/<action>(/<id>))');

$router->process('users:edit/12');
$router->process('users2/view/1231');
$router->process('orders_stats.view.555');
*/
