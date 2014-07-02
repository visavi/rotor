<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
if (!defined('BASEDIR')) {
	header('Location: /index.php');
	exit;
}

class Validation{

    /*
    * @errors array
    */
    public $errors = array();

    /*
    * @the validation rules array
    */
    private $validation_rules = array();

    /**
     * @the constructor, duh!
     */
    public function __construct()
    {
    }

    /**
     * @run the validation rules
     * @access public
     */
    public function run($show_error = 0)
    {
        $total_errors = 0;
        /*** set the vars ***/
        foreach( new ArrayIterator($this->validation_rules) as $opt)
        {
            if (empty($show_error) || $show_error > $total_errors)
            {
                switch($opt['type'])
                {
                    case 'string':
                        $this->validateString($opt['var'], $opt['label'], $opt['min'], $opt['max'], $opt['required']);
                    break;

                    case 'numeric':
                        $this->validateNumeric($opt['var'], $opt['label'], $opt['min'], $opt['max'], $opt['required']);
                    break;

                    case 'max':
                        $this->validateMax($opt['var'], $opt['label']);
                    break;

                    case 'min':
                        $this->validateMin($opt['var'], $opt['label']);
                    break;

                    case 'equal':
                        $this->validateEqual($opt['var'], $opt['label']);
                    break;

                    case 'not_equal':
                        $this->validateNotEqual($opt['var'], $opt['label']);
                    break;

                    case 'empty':
                        $this->validateEmpty($opt['var'], $opt['label']);
                    break;

                     case 'not_empty':
                        $this->validateNotEmpty($opt['var'], $opt['label']);
                    break;

                     case 'in':
                        $this->validateIn($opt['var'], $opt['label']);
                    break;

                    case 'regex':
                        $this->validateRegex($opt['var'], $opt['label'], $opt['required']);
                    break;

                    case 'float':
                        $this->validateFloat($opt['var'], $opt['label'], $opt['required']);
                    break;

                    case 'url':
                        $this->validateUrl($opt['var'], $opt['label'], $opt['required']);
                     break;

                    case 'email':
                        $this->validateEmail($opt['var'], $opt['label'], $opt['required']);
                    break;

                    case 'bool':
                        $this->validateBool($opt['var'], $opt['label'], $opt['required']);
                    break;

                    default:
                        $this->errors[] = 'Ошибка! Не найден тип правила "'.$opt['type'].'"';
                }
            }
            else
            {
                break;
            }

            $total_errors = count($this->errors);
        }

        /*** execution with no errors ***/
        if (empty($total_errors)){
            return true;
        }
    }

    /**
     * @add a rule to the validation rules array
     *
     * @access public
     * @param string $type The type of variable
     * @param string $var The variable
     * @param string $label The label of variable
     * @param bool $required If the field is required
     * @param int $min The minimum length or range
     * @param int $max the maximum length or range
     */

    public function addRule($type, $var, $label, $required=false, $min=0, $max=0)
    {
        $this->validation_rules[] = array('type'=>$type, 'var'=>$var, 'label'=>$label, 'required'=>$required, 'min'=>$min, 'max'=>$max);
        return $this;
    }

    /**
     * @displays an error
     *
     * @access private
     * @param string $var The variable
     * @param string $error The error
     */
    public function addError($error)
    {
        $this->errors[] = 'Ошибка! '.$error;
    }

    /**
     * @validate a string
     *
     * @access private
     * @param string $var The variable
     * @param string $label The label of variable
     * @param int $min the minimum string length
     * @param int $max The maximum string length
     * @param bool $required
     */
    private function validateString($var, $label, $min=0, $max=0, $required=false)
    {
        if ($required == false && utf_strlen($var) == 0)
        {
            return true;
        }

        if (isset($var))
        {
            if (utf_strlen($var) < $min)
            {
                $this->errors[] = 'Ошибка! '.$label.' (Не менее '.$min.' симв.)';
            }
            elseif (utf_strlen($var) > $max)
            {
                $this->errors[] = 'Ошибка! '.$label.' (Не более '.$max.' симв.)';
            }
        }
    }

    /**
     * Checks whether numeric input has a minimum value
     *
     * @param   float|int
     * @param   string
     * @return  bool
     */
    private function validateMin($var, $label)
    {
        if (is_array($var) && count($var)==2 && $var[0] <= $var[1])
        {
            return true;
        }
        else
        {
            $this->errors[] = 'Ошибка! '.$label;
        }
    }

    /**
     * Checks whether numeric input has a maximum value
     *
     * @param   float|int
     * @param   string
     * @return  bool
     */
    private function validateMax($var, $label)
    {
        if (is_array($var) && count($var)==2 && $var[0] >= $var[1])
        {
            return true;
        }
        else
        {
            $this->errors[] = 'Ошибка! '.$label;
        }
    }

    /**
     * @validate an number
     *
     * @access private
     * @param string $var the variable
     * @param string $label The label of variable
     * @param int $min The minimum number range
     * @param int $max The maximum number range
     * @param bool $required
     *
     */
    private function validateNumeric($var, $label, $min=0, $max=0, $required=false)
    {
        if ($required == false && utf_strlen($var) == 0)
        {
            return true;
        }

        if (filter_var($var, FILTER_VALIDATE_INT, array("options" => array("min_range"=>$min, "max_range"=>$max)))===FALSE)
        {
            $this->errors[] = 'Ошибка! '.$label ;
        }
    }

    /**
     * @validate a equality
     *
     * @access private
     * @param array $var list of variables
     * @param string $label The label of variable
     */
    private function validateEqual($var, $label)
    {

        if (is_array($var) && count($var)==2 && $var[0] === $var[1])
        {
            return true;
        }
        else
        {
            $this->errors[] = 'Ошибка! '.$label;
        }

    }

    /**
     * @validate the inequality
     *
     * @access private
     * @param array $var list of variables
     * @param string $label The label of variable
     */
    private function validateNotEqual($var, $label)
    {
        if (is_array($var) && count($var)==2 && $var[0] !== $var[1])
        {
            return true;
        }
        else
        {
            $this->errors[] = 'Ошибка! '.$label;
        }

    }

    /**
     * @validate is empty
     *
     * @access private
     * @param string $var the variable
     * @param string $label The label of variable
     */
    private function validateEmpty($var, $label)
    {
        if (!empty($var))
        {
            $this->errors[] = 'Ошибка! '.$label;
        }

    }

    /**
     * @validate is not empty
     *
     * @access private
     * @param string $var the variable
     * @param string $label The label of variable
     */
    private function validateNotEmpty($var, $label)
    {
        if (empty($var))
        {
            $this->errors[] = 'Ошибка! '.$label;
        }

    }

    /**
     * @validate is InArray
     *
     * @access private
     * @param array $var list of variables
     * @param string $label The label of variable
     */
    private function validateIn($var, $label)
    {
        if (is_array($var) && count($var)==2 && in_array($var[0], $var[1]))
        {
            return true;
        }
        else
        {
            $this->errors[] = 'Ошибка! '.$label;
        }
    }

    /**
     * @validate on a regular expression
     *
     * @access private
     * @param string $var the variable
     * @param string $label The label of variable
     * @param bool $required
     */
    private function validateRegex($var, $label, $required=false)
    {
        if (is_array($var) && count($var) == 2 && $required == false && utf_strlen($var[0]) == 0)
        {
            return true;
        }

        if (!preg_match($var[1], $var[0])) {
            $this->errors[] = 'Ошибка! '.$label;
        }
    }

    /**
     * @validate a floating point number
     *
     * @access private
     * @param $var The variable
     * @param string $label The label of variable
     * @param bool $required
     */

    private function validateFloat($var, $label, $required=false)
    {
        if ($required == false && utf_strlen($var) == 0)
        {
            return true;
        }
        if (filter_var($var, FILTER_VALIDATE_FLOAT) === false)
        {
            $this->errors[] = 'Ошибка! '.$label;
        }
    }

    /**
     * @validate a url
     *
     * @access private
     * @param string $var The variable
     * @param string $label The label of variable
     * @param bool $required
     */
    private function validateUrl($var, $label, $required=false)
    {
        if ($required == false && utf_strlen($var) == 0)
        {
            return true;
        }
        if (!preg_match('#^http://([а-яa-z0-9_\-\.])+(\.([а-яa-z0-9\/])+)+$#u', $var))
        //if (filter_var($var, FILTER_VALIDATE_URL) === FALSE)
        {
            $this->errors[] = 'Ошибка! '.$label;
        }
    }

    /**
     * @validate an email address
     *
     * @access private
     * @param string $var The variable
     * @param string $label The label of variable
     * @param bool $required
     */
    private function validateEmail($var, $label, $required=false)
    {
        if ($required == false && utf_strlen($var) == 0)
        {
            return true;
        }
        if (!preg_match('#^([a-z0-9_\-\.])+\@([a-z0-9_\-\.])+(\.([a-z0-9])+)+$#', $var))
        //if (filter_var($var, FILTER_VALIDATE_EMAIL) === FALSE)
        {
            $this->errors[] = 'Ошибка! '.$label;
        }
    }

    /**
     * @validate a boolean
     *
     * @access private
     * @param string $var the variable
     * @param string $label The label of variable
     * @param bool $required
     */
    private function validateBool($var, $label, $required=false)
    {
        if ($required == false && utf_strlen($var) == 0)
        {
            return true;
        }
        filter_var($var, FILTER_VALIDATE_BOOLEAN);
        {
            $this->errors[] = 'Ошибка! '.$label;
        }
    }

}
?>
