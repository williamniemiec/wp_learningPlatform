<?php
declare (strict_types=1);

namespace models\enum;


use models\util\Enumeration;


/**
 * Contains course 'order by' options.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class CourseOrderByEnum extends Enumeration 
{
    //-------------------------------------------------------------------------
    //        Enumerations
    //-------------------------------------------------------------------------
    public const NAME = 'name';
    public const TOTAL_STUDENTS = 'total_students';
}