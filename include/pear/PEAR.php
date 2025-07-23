<?php
/**
 * Modern PEAR Compatibility Layer for PHP 8.4
 * Minimal implementation to replace legacy PEAR functionality
 */

// Define PEAR constants
if (!defined('PEAR_ERROR_RETURN')) {
    define('PEAR_ERROR_RETURN', 1);
}
if (!defined('PEAR_ERROR_PRINT')) {
    define('PEAR_ERROR_PRINT', 2);
}
if (!defined('PEAR_ERROR_TRIGGER')) {
    define('PEAR_ERROR_TRIGGER', 4);
}
if (!defined('PEAR_ERROR_DIE')) {
    define('PEAR_ERROR_DIE', 8);
}
if (!defined('PEAR_ERROR_CALLBACK')) {
    define('PEAR_ERROR_CALLBACK', 16);
}
/**
 * Modern PEAR base class
 */
class PEAR
{
    protected $_debug = false;
    protected $_default_error_mode = PEAR_ERROR_RETURN;
    protected $_default_error_options = null;
    protected $_error_class = 'PEAR_Error';
    
    public function __construct($error_class = null)
    {
        if ($error_class !== null) {
            $this->_error_class = $error_class;
        }
    }
    
    // Legacy constructor for PHP < 7
    public function PEAR($error_class = null)
    {
        $this->__construct($error_class);
    }
    
    public function setErrorHandling($mode = null, $options = null)
    {
        if ($mode !== null) {
            $this->_default_error_mode = $mode;
        }
        if ($options !== null) {
            $this->_default_error_options = $options;
        }
    }
    
    public function raiseError($message = null, $code = null, $mode = null, $options = null, $userinfo = null)
    {
        if ($mode === null) {
            $mode = $this->_default_error_mode;
        }
        
        $error = new PEAR_Error($message, $code, $mode, $options, $userinfo);
        
        switch ($mode) {
            case PEAR_ERROR_DIE:
                die($message);
                break;
            case PEAR_ERROR_PRINT:
                echo $message . "\n";
                break;
            case PEAR_ERROR_TRIGGER:
                trigger_error($message, E_USER_ERROR);
                break;
            case PEAR_ERROR_RETURN:
            default:
                return $error;
        }
    }
    
    public static function isError($data)
    {
        return is_object($data) && is_a($data, 'PEAR_Error');
    }
}

/**
 * Modern PEAR_Error class
 */
class PEAR_Error
{
    protected $message;
    protected $code;
    protected $mode;
    protected $options;
    protected $userinfo;
    
    public function __construct($message = 'unknown error', $code = null, $mode = null, $options = null, $userinfo = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->mode = $mode;
        $this->options = $options;
        $this->userinfo = $userinfo;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function getCode()
    {
        return $this->code;
    }
    
    public function getMode()
    {
        return $this->mode;
    }
    
    public function getUserInfo()
    {
        return $this->userinfo;
    }
    
    public function toString()
    {
        return $this->message;
    }
    
    public function __toString()
    {
        return $this->message;
    }
}
// Global PEAR functions
function &PEAR_Singleton($class = 'PEAR', $params = array())
{
    static $instances = array();
    $key = md5(serialize(array($class, $params)));
    
    if (!isset($instances[$key])) {
        $instances[$key] = new $class($params);
    }
    
    return $instances[$key];
}

function PEAR_isError($data)
{
    return PEAR::isError($data);
}

echo "<!-- Modern PEAR Compatibility Layer loaded -->\n";
