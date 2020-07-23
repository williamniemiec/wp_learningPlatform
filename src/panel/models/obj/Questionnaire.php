<?php
namespace models\obj;


/**
 * Responsible for representing questionnaire-type classes.
 *
 * @author		William Niemiec &lt; williamniemiec@hotmail.com &gt;
 * @version		1.0.0
 * @since		1.0.0
 */
class Questionnaire extends _Class
{
    //-------------------------------------------------------------------------
    //        Attributes
    //-------------------------------------------------------------------------
    private $question;
    private $q1;
    private $q2;
    private $q3;
    private $q4;
    private $answer;
    
    
    //-------------------------------------------------------------------------
    //        Constructor
    //-------------------------------------------------------------------------
    /**
     * Creates a representation of a questionnaire-type class.
     * 
     * @param       int $id_module Module id that the class belongs to
     * @param       int $class_order Class order inside the module to which the
     * class belongs
     * @param       string $question Questionnaire question
     * @param       string $q1 First response option
     * @param       string $q2 Second response option
     * @param       string $q3 Third response option
     * @param       string $q4 Fourth response option
     * @param       int $answer Questionnaire answer (number between [1;4])
     */
    public function __construct($id_module, $class_order, $question, $q1, $q2, $q3, $q4, $answer)
    {
        $this->id_module = $id_module;
        $this->class_order = $class_order;
        $this->question = $question;
        $this->q1 = $q1;
        $this->q2 = $q2;
        $this->q3 = $q3;
        $this->q4 = $q4;
        $this->answer = $answer;
    }
    
    
    //-------------------------------------------------------------------------
    //        Getters
    //-------------------------------------------------------------------------
    /**
     * Gets questionnaire question.
     * 
     * @return      string Questionnarie question
     */
    public function getQuestion()
    {
        return $this->question;
    }
    
    /**
     * Gets first response option.
     * 
     * @return      string First response option
     */
    public function getQ1()
    {
        return $this->q1;
    }
    
    /**
     * Gets second response option.
     *
     * @return      string Second response option
     */
    public function getQ2()
    {
        return $this->q2;
    }
    
    /**
     * Gets third response option.
     *
     * @return      string Third response option
     */
    public function getQ3()
    {
        return $this->q3;
    }
    
    /**
     * Gets fourth response option.
     *
     * @return      string Fourth response option
     */
    public function getQ4()
    {
        return $this->q4;
    }
    
    /**
     * Gets questionnaire answer.
     *
     * @return      int Questionnaire answer (number between [1;4])
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}