<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Ical
 * @subpackage Zend_Ical_Property
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @namespace
 */
namespace Zend\Ical\Property\Value;

use Zend\Ical\Component\Timezone;

/**
 * DateTime value.
 *
 * @category   Zend
 * @package    Zend_Ical
 * @subpackage Zend_Ical_Property
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class DateTime implements Value
{
    /**
     * Year.
     * 
     * @var integer
     */
    protected $year;

    /**
     * Month.
     * 
     * @var integer
     */
    protected $month;
    
    /**
     * Day.
     * 
     * @var integer
     */
    protected $day;
    
    /**
     * Hour.
     * 
     * @var integer
     */
    protected $hour;
    
    /**
     * Minute.
     * 
     * @var integer
     */
    protected $minute;
    
    /**
     * Second.
     * 
     * @var integer
     */
    protected $second;
    
    /**
     * Whether this DateTime is in UTC.
     * 
     * @var boolean
     */
    protected $isUtc;
    
    /**
     * Create a new datetime value.
     * 
     * @param  mixed   $dateTime
     * @param  boolean $isUtc
     * @return void
     */
    public function __construct($dateTime = null, $isUtc = true)
    {
        if ($dateTime === null) {
            $dateTime = time();
            $isUtc    = true;
        }
        
        $this->setDateTime($dateTime, $isUtc);
    }
    
    /**
     * Set datetime.
     * 
     * @param  mixed   $dateTime
     * @param  boolean $isUtc
     * @return self
     */
    public function setDateTime($dateTime, $isUtc = true)
    {
        if (is_numeric($dateTime)) {
            if ($isUtc) {
                $timestamp = (int) $dateTime;
            } else {
                $dateTimeString = date('YmdHis', $dateTime);
            }
        } elseif (is_array($dateTime)) {
            $values   = array();
            $required = array(
                'year'   => array(0, 9999),
                'month'  => array(1, 12),
                'day'    => array(1, 31),
                'hour'   => array(0, 23),
                'minute' => array(0, 59),
                'second' => array(0, 59),
            );
            
            foreach ($required as $key => $restrictions) {
                if (!isset($dateTime[$key]) || !is_numeric($dateTime[$key])) {
                    throw new Exception\InvalidArgumentException(sprintf('Supplied datetime array is missing %s element', $key));
                } elseif ($dateTime[$key] < $restrictions[0]) {
                    throw new Exception\InvalidArgumentException(sprintf('%s element is lower than %d', $key, $restrictions[0]));
                } elseif ($dateTime[$key] > $restrictions[1]) {
                    throw new Exception\InvalidArgumentException(sprintf('%s element is greater than %d', $key, $restrictions[1]));
                }
                
                $values[] = (int) $dateTime[$key];
            }
            
            $dateTimeString = vsprintf('%04d%02d%02d%02d%02d%02d', $values);
        } elseif ($dateTime instanceof \DateTime) {
            if ($isUtc) {
                $timestamp = $dateTime->getTimestamp();
            } else {
                $dateTimeString = $dateTime->format('YmdHis');
            }
        } elseif ($dateTime instanceof \Zend\Date\DateObject) {
            if ($isUtc) {
                $timestamp = $dateTime->getTimestamp();
            } else {
                $dateTimeString = $dateTime->toString('yyyyMMddHHmmss');
            }
        } else {
            throw new Exception\InvalidArgumentException('Supplied datetime is neither a unix timestamp, an array nor an instance of \DateTime or \Zend\Date\Date');
        }
        
        if (isset($timestamp)) {
            $dateTimeString = gmdate('YmdHis', $timestamp);
        }
        
        sscanf(
            $dateTimeString, '%04d%02d%02d%02d%02d%02d',
            $this->year, $this->month, $this->day, $this->hour, $this->minute, $this->second
        );
        
        $this->isUtc = (bool) $isUtc;
        
        return $this;
    }
   
    /**
     * Check if the datetime is in UTC.
     * 
     * @return boolean
     */
    public function isUtc()
    {
        return $this->isUtc;
    }
    
    /**
     * Get unix timestamp representation.
     * 
     * @param  Timezone $timezone
     * @return integer
     */
    public function getTimestamp(Timezone $timezone = null)
    {
        if ($timezone === null) {
            if ($this->isUtc()) {
                // Fixed time
                return gmmktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
            } else {
                // Floating time (relative to the user)
                return mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
            }
        } else {
            
        }
    }
    
    /**
     * fromString(): defined by Value interface.
     * 
     * @see    Value::fromString()
     * @param  string $string
     * @return Value
     */
    public static function fromString($string)
    {
        if (!preg_match('(^(?<year>\d{4})(?<month>\d{2})(?<day>\d{2})T(?<hour>\d{2})(?<minute>\d{2})(?<second>\d{2})(?<UTC>Z)?$)S', $string, $match)) {
            return null;
        }
        
        return new self($match, isset($match['UTC']));
    }
}
