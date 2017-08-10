<?php

class fileHandler {

  var $modus;
  var $filename;
  var $filehandle;

  var $lastError;

  var $readModi     =array("r");
  var $writeModi    =array("w","a","x","c");
  var $bothModi     =array("r+","w+","a+","x+","c+");
  var $createModi   =array("w","a","w+","a+","x","x+","c","c+");
  var $exceptionModi=array("x","x+");


  /**
   * create instance of class
   * @param      string  $modus     The modus
   * @param      string  $filename  The filename
   */
  public function __construct($filename,$modus="r+") {
    $this->filename=$filename;
    if($modus!=false){
      $this->modus=$modus;
      $this->open($filename,$modus);
    }
  }

  /**
   * destruct class
   */
  public function __destruct() {
    If ($this->filehandle!=null) {
      $this->close();
    }
  }

  /**
   * opens a file
   * @param      string   $modus     The modus
   * @param      string   $filename  The filename
   * @return     boolean              true if file could be open succesfull
   */
  public function open($filename=false,$modus="r+") {
    if ($this->filehandle!=null)
      $this->close();
    if($filename!=false)
      $this->filename=$filename;
    $this->modus=$modus;

    //open just for reading
    if(in_array($this->modus, $this->readModi)){
      if (!@file_exists($this->filename)) { 
          $this->lastError='file '.$this->filename.' not exists';
          $this->filehandle=null;
          return false;
        }
        if (!@is_readable($this->filename)) {
          $this->lastError='file is not readable';
          $this->filehandle=null;
          return false;
        }
        $this->filehandle=@fopen($this->filename,$this->modus);
        if ($this->filehandle===false) { 
          $this->lastError='file could not open readonly';
          $this->filehandle=null;
          return false;
        } else {
          return true;          
        }
    } else {
      if (in_array($this->modus, $this->exceptionModi) && file_exists($this->filename)) {
        $this->lastError='in modi: '.$this->modus.' file isn\'t allowed to exists';
        return false;
      } else if ((file_exists($this->filename)) && (!is_writeable($this->filename))) {
        $this->lastError='file exists but could not be edit';
        return false;
      } else if (!is_writeable(substr($this->filename,0,strlen($this->filename)-strlen(basename($this->filename))-1))) {
        $this->lastError='file could not created';
        return false;
      }
      $this->filehandle=@fopen($this->filename,$this->modus);
      if ($this->filehandle===false) { 
        $this->lastError='file could not open writeable';
        $this->filehandle=null;
        return false;
      } else {
        return true;          
      }
    }
  }

  /**
   * close a file
   * @return     boolean  true if file could closed
   */
  public function close() {
    If ($this->filehandle==null) {
       $this->lastError='no file opened';
      return false;
    }
    If (@fclose($this->filehandle)) {
      $this->filehandle=null;
      return true;
    } else {
       $this->lastError='could not close file';
      return false;
    }
  }

  /**
   * proove if file pointer is on end of file
   * @return     boolean  true if is on end of file
   */
  public function eof() {
    If ($this->filehandle==null) {
      $this->lastError='no file opened';
      return false;
    }
    return @feof($this->filehandle);
  }

  /**
   * Reads a line.
   * @return     string|boolean  line as string or false if an error occourse
   */
  public function readLine() {
    if ($this->filehandle==null) {
      $this->lastError='no file opened';
      return false;
    }
    if (!in_array($this->modus,$this->readModi) && !in_array($this->modus,$this->bothModi)) {
      $this->lastError='wrong modus ('.$this->modus.'); read expected';
      return false;
    }
    $text=@fgets($this->filehandle);
    if (($text===false) && (!$this->eof())) {
      $this->lastError='could not read the next line';
      return false;
    } else {
      return $text;
    }
  }

  /**
   * Writes a line.
   * @param      string   $line   The line
   * @return     boolean          true if line could written
   */
  public function writeLine($line) {
    if ($this->filehandle==null) {
      $this->lastError='no file opened';
      return false;
    }
    if (!in_array($this->modus,$this->writeModi) && !in_array($this->modus,$this->bothModi)) {
      $this->lastError='wrong modus ('.$this->modus.'); write expected';
      return false;
    }
    $text=@fputs($this->filehandle,$line."\n"); # Zeile schreiben
    if ($text===false) { # konnten Zeile geschreiben werden?
      $this->lastError='could not write a line';
      return false;
    } else { # Zeile wurde erfolgreich geschrieben
      return true;
    }
  }

  /**
   * Reads a file.
   * @param      string         $filename  The filename
   * @param      string          $return    The return
   * @return     boolean|string             boolean if an error occourse. 
   *                                        Content as string if no error occourse
   */
  public function readFile($filename=false,$return="string",$mode="r") {
    if($filename==false && $this->open($filename,$mode)){ //error occourse in open function
      return false;
    } else if($this->filehandle==null) {
      $this->lastError='no file opened';
      return false;
    } 
    $text='';
    while ($this->eof()==false) {
      $line=$this->readLine();
      If (($line!==false) && ($line!='')) {
        if ($text!='') $text.="\n";
        $text.=$line;
      }
    }
    if($return=="array")
      $text=explode("\n",$text);
    return $text;
  }

  /**
   * Writes a file.
   * @param      string   $text      The text
   * @param      string   $filename  The filename
   * @return     boolean             true if file could be written
   */
  public function writeFile($text,$filename=false,$mode="r+") {
    if($filename==false && $this->open($filename,$mode)){ //error occourse in open function
      return false;
    } else if($this->filehandle==null) {
      $this->lastError='no file opened';
      return false;
    }
    $text=explode("\n",$text);
    Foreach ($text as $line) {
      If ($this->writeLine($line)==false) {
        return false;
      }
    }
    return true;
  }




/*************************************************/
/*        SOME OTHER MAGIC STUFF - BETA          */
/*************************************************/






  public function stat($filename=false){
    if($filename==false)
      $filename=$this->filename;
    if(!file_exists($filename)){
      $this->lastError='file does not exists';
      return false;
    } else {
      return stat($filename);
    }
  }

  public function chown($user,$filename=false){
    if($filename==false)
      $filename=$this->filename;
    if(!file_exists($filename)){
      $this->lastError='file does not exists';
      return false;
    } else {
      return chown($filename,$user);
    }
  }

  public function chgrp($group,$filename=false){
    if($filename==false)
      $filename=$this->filename;
    if(!file_exists($filename)){
      $this->lastError='file does not exists';
      return false;
    } else {
      return chgrp($filename,$group);
    }
  }

  public function chmod($mode,$filename=false){
    if($filename==false)
      $filename=$this->filename;
    if(!file_exists($filename)){
      $this->lastError='file does not exists';
      return false;
    } else {
      return chmod($filename,$mode);
    }
  }

  public function copy($dest,$filename=false){
    if($filename==false)
      $filename=$this->filename;
    if(!file_exists($filename)){
      $this->lastError='file does not exists';
      return false;
    } else {
      return copy($filename,$mode);
    }
  }

  public function symlink($link,$filename=false){
    if($filename==false)
      $filename=$this->filename;
    if(!file_exists($filename)){
      $this->lastError='file does not exists';
      return false;
    } else {
      return symlink($filename,$link);
    }
  }

  public function rename($newname,$filename=false){
    if($filename==false){
      $filename=$this->filename;
      if($this->filehandle!=null)
        $this->close();
    }
    if(!file_exists($filename)){
      $this->lastError='file does not exists';
      return false;
    } 
    else {
      return rename($filename,$newname);
    }
  }

  public function unlink($filename=false){
    if($filename==false){
      $filename=$this->filename;
      if($this->filehandle!=null)
        $this->close();
    }
    if(!file_exists($filename)){
      $this->lastError='file does not exists';
      return false;
    } 
    else {
      return unlink($filename,$mode);
    }
  }
}

?>