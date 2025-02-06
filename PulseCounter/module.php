<?php

declare(strict_types=1);

class PulseCounter extends IPSModule {
    
    public function Create() {
        parent::Create();
        
        //Properties
        $this->RegisterPropertyInteger('InputVariable', 0);
        $this->RegisterPropertyInteger('InputValue', 0);
        $this->RegisterPropertyInteger('Duration', 1);
        $this->RegisterPropertyInteger('Limit', 1);
        
        //Attributes
        $this->RegisterAttributeInteger('UpdateInterval', 5); // In Seconds
        $this->RegisterAttributeInteger('PulseCounter', 0);
        
        //Timers
        $this->RegisterTimer('CounterTimer', 0, "MPC_StopCounterAndReset(\$_IPS['TARGET']);");
        $this->RegisterTimer('UpdateRemainingTimer', 0, "MPC_UpdateRemaining(\$_IPS['TARGET']);");
        
        //Variables
        $this->RegisterVariableBoolean('Result', 'Ergebnis');
        $this->RegisterVariableInteger('Counter', 'Counter');
        $this->RegisterVariableString('Remaining', 'Restlaufzeit');
    }
    
    public function ApplyChanges() {
        parent::ApplyChanges();
        
        if($this->ReadPropertyInteger("InputVariable") > 0) {
            $this->RegisterMessage($this->ReadPropertyInteger("InputVariable"), VM_UPDATE);
        }
    }
    
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        //https://www.symcon.de/en/service/documentation/developer-area/sdk-tools/sdk-php/messages/
        if ($Message == VM_UPDATE) {
            // if timer not running, then start it
            if ($this->GetTimerInterval('CounterTimer') === 0) {
                $this->StartTimer();
            }
            
            $this->countUp($Data);
            $this->verify();
        }
    }
    
    private function countUp($Data) {        
        // $Data[0] Neuer Wert
        // $Data[1] true/false ob Änderung oder Aktualisierung.
        // $Data[2] Alter Wert 
        
        $count = false;
        switch ($this->ReadPropertyInteger('InputValue')) {
            case 0: // true
                if ($Data[0] === true) { $count = true; }
                break;
            case 1: // false
                if ($Data[0] === false) { $count = true; }
                break;
            case 2: // true and false
                if (($Data[0] === false) || ($Data[0] === true)) { $count = true; }
                break;
        }
        
        if ($count == true) {
            $this->WriteAttributeInteger('PulseCounter', $this->ReadAttributeInteger('PulseCounter') + 1);
            $this->SetValue('Counter', $this->ReadAttributeInteger('PulseCounter'));
        }
    }
    
    private function verify() {
        if ($this->ReadAttributeInteger('PulseCounter') >= $this->ReadPropertyInteger("Limit")) {
            // Limit reached
            $this->StopCounter(true);
        }
    }
    
    private function StartTimer() {
        //Start CounterTimer
        $duration = $this->ReadPropertyInteger('Duration');
        $this->SetTimerInterval('CounterTimer', $duration  * 1000);
        
        //Update display variable periodically 
        $this->SetTimerInterval('UpdateRemainingTimer', 1000 * $this->ReadAttributeInteger('UpdateInterval'));
        $this->UpdateRemaining();
        
        $this->initCounterValues();
    }
    
    private function initCounterValues() {
        $this->SetValue('Counter', 0);
        $this->WriteAttributeInteger('PulseCounter', 0);
        if ($this->GetValue('Result') !== false) { $this->SetValue('Result', false); }
    }
    
    private function StopCounter(bool $result) {
        // Only Stop Timer with givven result, but do not reset anything
        $this->SetValue('Result', $result);
        $this->SetTimerInterval('CounterTimer', 0);
        $this->SetTimerInterval('UpdateRemainingTimer', 0);
        $this->SetValue('Remaining', '00:00:00');
    }
    
    public function StopCounterAndReset() {
        // If running till end without any result, set all values back to 0
        $this->StopCounter(false);
        $this->initCounterValues();
    }
    
    public function UpdateRemaining() {
        // Refresh Remaining Variable
        $secondsRemaining = 0;
        foreach (IPS_GetTimerList() as $timerID) {
            $timer = IPS_GetTimer($timerID);
            if (($timer['InstanceID'] == $this->InstanceID) && ($timer['Name'] == 'CounterTimer')) {
                $secondsRemaining = $timer['NextRun'] - time();
                break;
            }
        }
        
        //Display remaining time as string
        $this->SetValue('Remaining', sprintf('%02d:%02d:%02d', ($secondsRemaining / 3600), ($secondsRemaining / 60 % 60), $secondsRemaining % 60));
    }
    
}
?>