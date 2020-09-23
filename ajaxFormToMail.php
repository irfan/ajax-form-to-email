<?php
/**
 * I wrote as indiscriminately.
 * 
 * You must add better validation
 * 
 * Author:
 * Irfan Durmus
 * http://irfandurmus.com
 * 
 * Usage:
 * Send data below by http post;
 * name, email, subject, message
 * fill $destination variable with your e-mail address.
 * 
 * You will get json response like that;
 * {success:true, errors:[]}
 * OR if any error occur then
 * {success:false, errors:['name':'Invalid name']}
 * 
 */

class ajaxFormToMail {
    
    // write your e-mail address
    protected $destination = '';

    protected $data = array();
    protected $errors = array();
    protected $response = array();
        
    protected $messages = array(
        'subject'   => 'Invalid subject',
        'message'   => 'Invalid message',
        'email'     => 'Invalid email',
        'name'      => 'Invalid name'
    );


    public function __construct(){
        
        if($_SERVER['REQUEST_METHOD'] != 'POST')
        {
            exit('Do you trying something bad? Its not funny.');
        }
        else
        {
            $this->setData($_POST)
                ->checkName()
                ->checkMail()
                ->checkSubject()
                ->checkMessage();
            
            if (count($this->getErrors()) < 1)
            {
                $this->sendMail();
            }
            else
            {
                $this->putErrors();
            }

        }

    }
    
    protected function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    
    
    protected function getField($name)
    {
        return $this->data[$name];
    }
    
    protected function setError($key, $message)
    {
        $this->errors[$key] = $message;
        return $this;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    protected function checkName(){
        $name = $this->getField('name');
        
        if (strlen($name) < 5)
        {
            $this->setError('name', $this->messages['name']);
        }
        return $this;
    }
    
    
    protected function checkMail()
    {
        $email = $this->getField('email');
        $regex = '/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/';
        
        if (!preg_match($regex, $email))
        {
            $this->setError('email', $this->messages['email']);
        }
        return $this;
    }
    
    
    
    protected function checkSubject()
    {
        $subject = $this->getField('subject');
        
        if (strlen($subject) < 5)
        {
            $this->setError('subject', $this->messages['subject']);
        }
        return $this;
    }
    
    
    protected function checkMessage()
    {
        $message = $this->getField('message');
        
        if (strlen($message) < 10)
        {
            $this->setError('message', $this->messages['message']);
        }
        return $this;
    }
    
    public function putErrors(){
        
        $errors = $this->getErrors();
        
        $response = array(
            'errors' => $errors,
            'success' => false
        );
        
        $this->response = $response;
        return $this;
    }
    
    protected function sendMail(){
        
        $email = $this->getField('email');
        $subject = '[ Your Form ] - ' . $this->getField('subject');
        $message = $this->getField('message') . "\r\n\r\n" . $this->getField('name');
        
        $headers =  "From: $email\n";
        $headers .= "Content-Type: text/html; charset=utf-8";
        
        mail($this->destination, $subject, $message, $headers);
        
        $response = array(
            'errors' => array(),
            'success' => true
        );
        
        $this->response = $response;
        return $this;
    }
    
    public function getJsonResponse()
    {
        header('Content-type: application/json');
        return json_encode($this->response);
    }
}

$validate = new ajaxFormToMail;
echo $validate->getJsonResponse();
