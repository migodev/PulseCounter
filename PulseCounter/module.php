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
        $this->RegisterAttributeFloat('LastCountTime', 0);
        
        //Timers
        $this->RegisterTimer('CounterTimer', 0, "MPC_StopCounterAndReset(\$_IPS['TARGET']);");
        $this->RegisterTimer('UpdateRemainingTimer', 0, "MPC_UpdateRemaining(\$_IPS['TARGET']);");
        
        //Variables
        $this->RegisterVariableBoolean('Result', $this->Translate('Result'));
        $this->RegisterVariableInteger('Counter', $this->Translate('Counter'));
        $this->RegisterVariableString('Remaining', $this->Translate('Remaining Time'));
        $this->RegisterVariableInteger('Difference', $this->Translate('Difference Time'), ['PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, "SUFFIX" => "ms"]);
    }
    
    public function ApplyChanges() {
        parent::ApplyChanges();
        
        //Unregister all messages
        $messageList = array_keys($this->GetMessageList());
        foreach ($messageList as $message) {
            $this->UnregisterMessage($message, VM_UPDATE);
        }
        
        if($this->ReadPropertyInteger("InputVariable") > 0) {
            $this->RegisterMessage($this->ReadPropertyInteger("InputVariable"), VM_UPDATE);
        }
    }
    
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        //https://www.symcon.de/en/service/documentation/developer-area/sdk-tools/sdk-php/messages/
        if ($Message == VM_UPDATE) {          
            $this->CountUp($Data);
        }
    }
    
    private function CountUp($Data) {        
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
            // if timer not running, then start it
            if ($this->GetTimerInterval('CounterTimer') === 0) {
                $this->StartTimer();
            }
            
            // calculate difference between Counts
            $lct = $this->ReadAttributeFloat('LastCountTime');
            $this->WriteAttributeFloat('LastCountTime', microtime(true));
            if ($lct == 0) {
                $this->SetValue('Difference', 0);
            } else {
                $now = microtime(true);
                $this->SetValue('Difference', ($now - $lct)*1000); //in milliseconds
            }
            
            $this->WriteAttributeInteger('PulseCounter', $this->ReadAttributeInteger('PulseCounter') + 1);
            $this->SetValue('Counter', $this->ReadAttributeInteger('PulseCounter'));
            
            // Verfify if Count Limit already reached
            $this->Verify();
        }
    }
    
    private function Verify() {
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
        
        $this->InitCounterValues();
    }
    
    private function InitCounterValues() {
        $this->SetValue('Counter', 0);
        $this->WriteAttributeInteger('PulseCounter', 0);
        $this->SetValue('Difference', 0);
        $this->WriteAttributeFloat('LastCountTime', 0);
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
        $this->InitCounterValues();
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