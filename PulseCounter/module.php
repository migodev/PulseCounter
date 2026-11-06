<?php

declare(strict_types=1);

class PulseCounter extends IPSModuleStrict {
    
    public function Create(): void {
        parent::Create();
        
        //Properties
        $this->RegisterPropertyInteger('InputVariable', 0);
        $this->RegisterPropertyInteger('InputValue', 0);
        $this->RegisterPropertyInteger('Duration', 1);
        $this->RegisterPropertyInteger('Limit', 1);
        $this->RegisterPropertyInteger('UpdateInterval', 5);// In Seconds
        $this->RegisterPropertyInteger('ChangeMode', 1);
        
        //Attributes
        $this->RegisterAttributeInteger('PulseCounter', 0);
        $this->RegisterAttributeFloat('LastCountTime', 0);
        
        //Timers
        $this->RegisterTimer('CounterTimer', 0, "MPC_StopCounterAndReset(\$_IPS['TARGET']);");
        
        //Variables
        $this->RegisterVariableBoolean('Result', $this->Translate('Result'));
        $this->RegisterVariableInteger('Counter', $this->Translate('Counter'));
        $this->RegisterVariableInteger('Remaining', $this->Translate('Remaining Time'), ['PRESENTATION' => VARIABLE_PRESENTATION_DURATION, 'COUNTDOWN_TYPE' => 1 /* Until value in variable */], 50);
        $this->RegisterVariableInteger('Difference', $this->Translate('Difference Time'), ['PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION, "SUFFIX" => "ms"]);
    }
    
    public function ApplyChanges(): void {
        parent::ApplyChanges();
        
        //Unregister all messages
        $messageList = array_keys($this->GetMessageList());
        foreach ($messageList as $message) {
            $this->UnregisterMessage($message, VM_UPDATE);
        }
        
        //Delete all references in order to readd them
        foreach ($this->GetReferenceList() as $referenceID) {
            $this->UnregisterReference($referenceID);
        }
        
        if($this->ReadPropertyInteger("InputVariable") > 0) {
            $this->RegisterMessage($this->ReadPropertyInteger("InputVariable"), VM_UPDATE);
            $this->RegisterReference($this->ReadPropertyInteger("InputVariable"));
        }
    }
    
    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void {
        //https://www.symcon.de/en/service/documentation/developer-area/sdk-tools/sdk-php/messages/
        if ($Message == VM_UPDATE) {          
            $this->CountUp($Data);
        }
    }
    
    private function CountUp(Array $Data): bool {        
        // $Data[0] Neuer Wert
        // $Data[1] true/false ob Änderung oder Aktualisierung.
        // $Data[2] Alter Wert 
        
        $changeMode = $this->ReadPropertyInteger("ChangeMode");
        if ($changeMode === 2) {
            // Only On Change
            if ($Data[1] === false) {
                // exit, because there is no Change
                $this->SendDebug('changeMode', "exit, because there is no Change", 0);
                return false;
            }
        }

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
        $this->SendDebug('InputValue', json_encode($this->ReadPropertyInteger('InputValue')), 0);
        $this->SendDebug('Count', json_encode($count), 0);

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
        return true;
    }
    
    private function Verify(): void {
        if ($this->ReadAttributeInteger('PulseCounter') >= $this->ReadPropertyInteger("Limit")) {
            // Limit reached
            $this->SendDebug('Limit', "Limit reached", 0);

            //Set only result and let the timer run
            $this->SetResult(true);
        } else {
            $this->SendDebug('Limit', "Limit not reached", 0);
        }
    }
    
    private function StartTimer(): void {
        //Start CounterTimer
        $duration = $this->ReadPropertyInteger('Duration');
        $this->SetTimerInterval('CounterTimer', $duration  * 1000);
        
        $this->SendDebug('timer', "Start Timer with Duration: ".$duration."s", 0);

        //Update display variable
        $this->SetValue('Remaining', time() + $this->ReadPropertyInteger('Duration'));

        $this->InitCounterValues();
    }
    
    private function InitCounterValues(): void {
        $this->SendDebug('init', "Set all values to 0 and init", 0);
        $this->SetValue('Counter', 0);
        $this->WriteAttributeInteger('PulseCounter', 0);
        $this->SetValue('Difference', 0);
        $this->WriteAttributeFloat('LastCountTime', 0);
        $this->SetResult(false);
    }
    
    private function SetResult(bool $Result): void {
        if ($this->GetValue('Result') != $Result) {
            $this->SendDebug('SetResult', json_encode($Result), 0);
            $this->SetValue('Result', $Result);
        }
    }
    
    private function StopCounter(bool $Result): void {
        // Only Stop Timer with givven result, but do not reset anything
        $this->SendDebug('StopCounter', "stopping", 0);
        $this->SetResult($Result);
        $this->SetTimerInterval('CounterTimer', 0);
        $this->SetValue('Remaining', 0);
    }
    
    public function StopCounterAndReset(): void {
        // If running till end without any result, set all values back to 0
        $this->SendDebug('StopCounterAndReset', "stopping & resetting", 0);
        $this->StopCounter(false);
        $this->InitCounterValues();
    }    
}
?>