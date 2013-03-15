<?php
class CommandChain {
    private $commands = array();
    public function addCommand($cmd) {
        if(is_array($cmd) && !empty($cmd)) {
            foreach($cmd as $c) {
                $this->commands[] = $c;
            }
        } else {
            $this->commands[] = $cmd;
        }
    }
    public function runCommand() {
        foreach ($this->commands as $command) {
            if(!$command->onCommand()){
                return false;
            }
        }
        return true;
    }
}
